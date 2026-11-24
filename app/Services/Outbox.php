<?php

namespace App\Services;

use App\Filament\Resources\InquiryResource;
use App\Mail\NewSchoolApplication;
use App\Mail\WorkflowMail;
use App\Models\GuardianAccessGrant;
use App\Models\Inquiry;
use App\Models\MailOutbox;
use App\Models\SchoolApplication;
use App\Models\Tree;
use App\Models\TreeAdoption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class Outbox
{
    public function add(string $event, string|int $reference, string $key, array $payload = []): MailOutbox
    {
        $locale = 'sr';
        if (!str_ends_with($event, '.admin')) {
            $recipient = str_starts_with($event, 'school.') ? SchoolApplication::find($reference) : (str_starts_with($event, 'adoption.') ? TreeAdoption::find($reference) : (str_starts_with($event, 'guardian.') ? GuardianAccessGrant::find($reference)?->adoption : null));
            $locale = in_array($recipient?->preferred_locale, ['sr','en'], true) ? $recipient->preferred_locale : 'sr';
        }
        return MailOutbox::firstOrCreate(['dedup_key' => $key], ['locale' => $locale, 'event' => $event, 'reference' => (string) $reference, 'payload' => $payload, 'available_at' => now()]);
    }

    public function drain(int $limit = 25): int
    {
        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $item = DB::transaction(function () {
                $item = MailOutbox::where(fn ($q) => $q->where('status', 'pending')->where('available_at', '<=', now())->orWhere(fn ($s) => $s->where('status', 'processing')->where('locked_until', '<=', now())))->orderBy('id')->lockForUpdate()->first();
                if (! $item) {
                    return null;
                }
                $item->update(['status' => 'processing', 'claim' => Str::random(40), 'locked_until' => now()->addMinutes(5), 'attempts' => $item->attempts + 1]);

                return $item;
            });
            if (! $item) {
                break;
            }
            try {
                $previousLocale = app()->getLocale();
                $previousDefaults = \Illuminate\Support\Facades\URL::getDefaultParameters();
                try {
                    \Illuminate\Support\Facades\URL::defaults(['locale' => $item->locale]);
                    app()->setLocale($item->locale);
                    $delivery = $this->delivery($item);
                } finally {
                    app()->setLocale($previousLocale);
                    \Illuminate\Support\Facades\URL::defaults(['locale' => $previousDefaults['locale'] ?? 'sr']);
                }
                if ($delivery === null) {
                    $this->finish($item, ['status' => 'cancelled', 'payload' => null]);

                    continue;
                }
                [$recipient,$mail] = $delivery;
                if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Notification recipient not configured');
                }
                Mail::to($recipient, str_ends_with($item->event, '.admin') ? config('ecosync.notification_name') : null)->send($mail->locale($item->locale));
                $transport = config('mail.default');
                $simulated = in_array($transport, ['log', 'array'], true) || ($transport === 'failover' && count(array_intersect(config('mail.mailers.failover.mailers', []), ['log', 'array'])) > 0);
                $this->finish($item, ['status' => $simulated ? 'logged' : 'sent', 'transport' => $transport, 'payload' => null, 'processed_at' => now(), 'last_error' => null]);
                if ($item->event === 'school.admin' && ! $simulated) {
                    SchoolApplication::whereKey($item->reference)->update(['notification_sent_at' => now()]);
                }
                $count++;
            } catch (\Throwable $exception) {
                $this->finish($item, ['status' => $item->attempts >= config('ecosync.mail_max_attempts') ? 'failed' : 'pending', 'available_at' => now()->addSeconds(min(3600, 60 * (2 ** min($item->attempts, 6)))), 'last_error' => $exception::class]);
                Log::warning('Outbox transport attempt failed.', ['outbox_id' => $item->id, 'exception_type' => $exception::class]);
            }
        }

        return $count;
    }

    private function finish(MailOutbox $item, array $data): void
    {
        MailOutbox::whereKey($item->id)->where('claim', $item->claim)->update($data + ['claim' => null, 'locked_until' => null]);
    }

    private function delivery(MailOutbox $item): ?array
    {
        if (str_starts_with($item->event, 'school.')) {
            $application = SchoolApplication::find($item->reference);
            if (! $application || ! $application->email || str_ends_with($application->email, '@example.invalid')) {
                return null;
            }
            if ($item->event === 'school.admin') {
                return [config('ecosync.notification_email'), new NewSchoolApplication($application)];
            }
            if ($item->event === 'school.ack') {
                return [$application->email, new WorkflowMail(__('public.ecosync_we_have_received_your_application'), [__('public.we_have_received_the_application_for_school').$application->school_name.__('public.ecosync_will_review_it_and_contact_the_school_representative_receipt')])];
            }
            $approved = $item->event === 'school.approved';

            return [$application->email, new WorkflowMail($approved ? __('public.ecosync_your_application_has_been_reviewed') : __('public.ecosync_school_application_update'), [$approved ? __('public.your_application_has_been_approved_for_further_arrangements_with_ecosync') : __('public.after_review_the_school_application_has_not_been_approved_at')])];
        }
        if ($item->event === 'inquiry.admin') {
            $inquiry = Inquiry::find($item->reference);
            if (! $inquiry || ! $inquiry->email) {
                return null;
            }

            return [config('ecosync.notification_email'), new WorkflowMail(__('public.ecosync_new_inquiry'), [
                __('public.type_2').$inquiry->kind, __('public.name').$inquiry->name, __('public.email_3').$inquiry->email, __('public.phone').($inquiry->phone ?: __('public.not_provided_2')),
                __('public.topic').$inquiry->topic, __('public.message_2').$inquiry->message,
            ], url: InquiryResource::getUrl('edit', ['record' => $inquiry], panel: 'admin'))];
        }
        if (in_array($item->event, ['guardian.access', 'guardian.email_change'], true)) {
            $grant = GuardianAccessGrant::find($item->reference);
            if (! $grant) {
                return null;
            }

            return DB::transaction(function () use ($grant) {
                $adoption = TreeAdoption::findOrFail($grant->tree_adoption_id);
                Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
                $adoption = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
                $grant = GuardianAccessGrant::whereKey($grant->id)->lockForUpdate()->firstOrFail();
                if ($adoption->status !== 'active' || $adoption->access_version !== $grant->access_version || $grant->consumed_at || $grant->expires_at->isPast()) {
                    return null;
                }
                $token = bin2hex(random_bytes(32));
                $grant->update(['token_hash' => hash('sha256', $token)]);

                if ($grant->purpose === 'email_change') {
                    return [$grant->new_email, new WorkflowMail(__('public.ecosync_verify_your_new_email_address'), [__('public.verify_your_new_email_address_verification_will_invalidate_all_previous')], route('guardian.email.form', $grant->public_id).'#token='.$token)];
                }

                return [$adoption->guardian_email, new WorkflowMail(__('public.ecosync_guardian_access'), [__('public.open_the_link_and_confirm_access_the_link_is_single')], route('guardian.access.form', $grant->public_id).'#token='.$token)];
            });
        }
        if (str_starts_with($item->event, 'adoption.')) {
            $adoption = TreeAdoption::find($item->reference);
            if (! $adoption || ! $adoption->guardian_email) {
                return null;
            }
            if ($item->event === 'adoption.verify') {
                return DB::transaction(function () use ($item, $adoption) {
                    Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
                    $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
                    if ($current->status !== 'pending_verification' || $current->verification_version !== ($item->payload['version'] ?? 0) || $current->verification_expires_at->isPast() || $current->reservation_expires_at->isPast()) {
                        return null;
                    }
                    $token = bin2hex(random_bytes(32));
                    $current->update(['verification_token_hash' => hash('sha256', $token)]);

                    return [$current->guardian_email, new WorkflowMail(__('public.ecosync_verify_your_tree_adoption_request'), [
                        __('public.verify_your_email_address_for_the_adoption_request_for_tree').$current->tree->public_identifier.__('public.an_administrator_will_review_the_request_after_verification_your_adoption'),
                        __('public.the_link_is_single_use_use_the_most_recent_message'),
                    ], route('adoptions.verify.form', $current->public_id).'#token='.$token)];
                });
            }
            $messages = [
                'adoption.approved' => [__('public.ecosync_your_adoption_is_approved'), __('public.your_tree_adoption_is_approved_to_access_your_tree_request')],
                'adoption.rejected' => [__('public.ecosync_adoption_update'), __('public.your_adoption_request_has_not_been_approved_at_this_time')],
                'adoption.ended' => [__('public.ecosync_adoption_ended'), __('public.your_tree_adoption_has_ended_thank_you_for_supporting_our')],
            ];
            if (isset($messages[$item->event])) {
                [$subject,$message] = $messages[$item->event];

                return [$adoption->guardian_email, new WorkflowMail($subject, [$message], route('guardian.request'))];
            }
        }

        return null;
    }
}
