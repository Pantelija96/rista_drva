<?php

namespace App\Services;

use App\Models\GuardianAccessGrant;
use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class Adoptions
{
    public static function emailHash(string $email): string
    {
        return hash_hmac('sha256', mb_strtolower(trim($email)), config('app.key'));
    }

    public function request(Tree $tree, array $data, string $submissionKey): TreeAdoption
    {
        return DB::transaction(function () use ($tree, $data, $submissionKey) {
            $tree = Tree::published()->whereKey($tree->id)->lockForUpdate()->firstOrFail();
            $existing = TreeAdoption::where('submission_key', $submissionKey)->first();
            if ($existing) {
                if ($existing->tree_id !== $tree->id) {
                    throw ValidationException::withMessages(['adoption' => __('public.please_reopen_the_form_for_the_selected_tree')]);
                }

                return $existing;
            }
            $this->expireTree($tree);
            if ($tree->adoption_status !== 'available' || TreeAdoption::where('reserved_tree_id', $tree->id)->exists()) {
                throw ValidationException::withMessages(['adoption' => __('public.this_tree_is_not_available_for_a_new_adoption_please')]);
            }
            $adoption = TreeAdoption::create(['preferred_locale' => app()->getLocale(), 
                ...$data, 'public_id' => (string) Str::uuid(), 'tree_id' => $tree->id, 'reserved_tree_id' => $tree->id, 'status' => 'pending_verification',
                'guardian_email' => mb_strtolower(trim($data['guardian_email'])), 'guardian_email_hash' => self::emailHash($data['guardian_email']),
                'verification_expires_at' => now()->addMinutes(config('adoptions.verification_minutes')),
                'reservation_expires_at' => now()->addMinutes(config('adoptions.reservation_minutes')),
                'consent_version' => config('adoptions.consent_version'), 'consented_at' => now(), 'submission_key' => $submissionKey,
            ]);
            $this->event($adoption, 'requested', null, 'visitor');
            app(Outbox::class)->add('adoption.verify', $adoption->id, 'adoption.verify:'.$adoption->id.':1', ['version' => 1]);

            return $adoption;
        });
    }

    public function verify(TreeAdoption $adoption, string $token): TreeAdoption
    {
        return DB::transaction(function () use ($adoption, $token) {
            $tree = Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $this->expireTree($tree);
            $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            if ($current->status !== 'pending_verification' || ! $current->verification_token_hash || $current->verification_expires_at->isPast() || ! hash_equals($current->verification_token_hash, hash('sha256', $token))) {
                throw ValidationException::withMessages(['token' => __('public.the_link_is_invalid_has_already_been_used_or_has')]);
            }
            $from = $current->status;
            $current->update(['status' => 'pending_approval', 'email_verified_at' => now(), 'verification_token_hash' => null, 'reservation_expires_at' => now()->addHours(config('adoptions.review_hours'))]);
            $this->event($current, 'email_verified', $from, 'guardian');

            return $current;
        });
    }

    public function resend(TreeAdoption $adoption, string $email): void
    {
        DB::transaction(function () use ($adoption, $email) {
            $tree = Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $this->expireTree($tree);
            $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            if ($current->status !== 'pending_verification' || ! hash_equals($current->guardian_email_hash, self::emailHash($email))) {
                return;
            }
            $version = $current->verification_version + 1;
            $expires = now()->addMinutes(config('adoptions.verification_minutes'))->min($current->reservation_expires_at);
            $current->update(['verification_token_hash' => null, 'verification_version' => $version, 'verification_expires_at' => $expires]);
            app(Outbox::class)->add('adoption.verify', $current->id, 'adoption.verify:'.$current->id.':'.$version, ['version' => $version]);
        });
    }

    public function transition(TreeAdoption $adoption, string $target, User $admin, ?string $reason = null): TreeAdoption
    {
        abort_unless($admin->is_admin, 403);

        return DB::transaction(function () use ($adoption, $target, $admin, $reason) {
            $tree = Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $this->expireTree($tree);
            $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            if ($current->status === $target) {
                return $current;
            }
            $from = $current->status;
            if ($target === 'active') {
                if ($from !== 'pending_approval' || ! $current->email_verified_at || $current->reserved_tree_id !== $tree->id || $tree->adoption_status !== 'available') {
                    throw ValidationException::withMessages(['status' => __('public.only_a_verified_reserved_request_for_an_available_tree_can')]);
                }
                $current->update(['status' => 'active', 'approved_at' => now(), 'approved_by' => $admin->id, 'reservation_expires_at' => null]);
                $tree->update(['adoption_status' => 'adopted']);
                app(Outbox::class)->add('adoption.approved', $current->id, 'adoption.approved:'.$current->id);
            } elseif ($target === 'rejected' && in_array($from, ['pending_verification', 'pending_approval'], true)) {
                $current->update(['status' => 'rejected', 'reserved_tree_id' => null, 'verification_token_hash' => null, 'rejected_at' => now(), 'rejection_reason' => $reason, 'access_version' => $current->access_version + 1]);
                app(Outbox::class)->add('adoption.rejected', $current->id, 'adoption.rejected:'.$current->id);
            } elseif ($target === 'ended' && $from === 'active') {
                $current->update(['status' => 'ended', 'reserved_tree_id' => null, 'ended_at' => now(), 'access_version' => $current->access_version + 1]);
                if ($tree->adoption_status === 'adopted') {
                    $tree->update(['adoption_status' => in_array($tree->physical_condition, ['removed', 'replaced'], true) ? 'unavailable' : 'available']);
                }
                app(Outbox::class)->add('adoption.ended', $current->id, 'adoption.ended:'.$current->id);
            } else {
                throw ValidationException::withMessages(['status' => __('public.this_status_change_is_not_allowed')]);
            }
            $this->event($current, 'status_changed', $from, 'admin', $admin->id);

            return $current;
        });
    }

    public function expireTree(Tree $tree): int
    {
        // Caller holds the tree row lock. Active adoptions are never expired by cleanup.
        $expired = TreeAdoption::where('reserved_tree_id', $tree->id)->whereIn('status', ['pending_verification', 'pending_approval'])->where('reservation_expires_at', '<=', now())->lockForUpdate()->get();
        foreach ($expired as $adoption) {
            $from = $adoption->status;
            $adoption->update(['status' => 'expired', 'reserved_tree_id' => null, 'verification_token_hash' => null, 'access_version' => $adoption->access_version + 1]);
            $this->event($adoption, 'reservation_expired', $from, 'system');
        }

        return $expired->count();
    }

    public function requestAccess(string $identifier, string $email): void
    {
        DB::transaction(function () use ($identifier, $email) {
            $tree = Tree::where('public_identifier', $identifier)->lockForUpdate()->first();
            if (! $tree) {
                return;
            }
            $adoption = TreeAdoption::where('tree_id', $tree->id)->where('status', 'active')->where('guardian_email_hash', self::emailHash($email))->lockForUpdate()->first();
            if (! $adoption) {
                return;
            }
            if (GuardianAccessGrant::where('tree_adoption_id', $adoption->id)->where('purpose', 'access')->whereNull('consumed_at')->where('expires_at', '>', now()->addMinutes(max(1, config('adoptions.access_minutes') - 2)))->exists()) {
                return;
            }
            $grant = GuardianAccessGrant::create(['public_id' => (string) Str::uuid(), 'tree_adoption_id' => $adoption->id, 'expires_at' => now()->addMinutes(config('adoptions.access_minutes')), 'access_version' => $adoption->access_version]);
            app(Outbox::class)->add('guardian.access', $grant->id, 'guardian.access:'.$grant->id);
        });
    }

    public function consumeAccess(GuardianAccessGrant $grant, string $token): TreeAdoption
    {
        return DB::transaction(function () use ($grant, $token) {
            $adoption = TreeAdoption::findOrFail($grant->tree_adoption_id);
            Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $adoption = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            $grant = GuardianAccessGrant::whereKey($grant->id)->lockForUpdate()->firstOrFail();
            if ($grant->purpose !== 'access' || $adoption->status !== 'active' || $grant->consumed_at || $grant->expires_at->isPast() || $grant->access_version !== $adoption->access_version || ! $grant->token_hash || ! hash_equals($grant->token_hash, hash('sha256', $token))) {
                throw ValidationException::withMessages(['token' => __('public.the_access_link_is_invalid_has_already_been_used_or')]);
            }
            $grant->update(['consumed_at' => now(), 'token_hash' => null]);

            return $adoption;
        });
    }

    public function updatePreferences(TreeAdoption $adoption, array $data): void
    {
        DB::transaction(function () use ($adoption, $data) {
            Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            abort_unless($current->status === 'active', 403);
            $previous = ['adoption_type' => $current->adoption_type, 'public_display_preference' => $current->public_display_preference];
            $current->update($data);
            $this->event($current, 'preferences_updated', $current->status, 'guardian', metadata: ['previous' => $previous, 'current' => ['adoption_type' => $current->adoption_type, 'public_display_preference' => $current->public_display_preference]]);
        });
    }

    public function requestEmailChange(TreeAdoption $adoption, string $email, ?User $admin = null): void
    {
        Validator::make(['email' => $email], ['email' => 'required|email|max:160'])->validate();
        if ($admin) {
            abort_unless($admin->is_admin, 403);
        }
        DB::transaction(function () use ($adoption, $email, $admin) {
            Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            abort_unless($current->status === 'active', 403);
            $grant = GuardianAccessGrant::create(['public_id' => (string) Str::uuid(), 'tree_adoption_id' => $current->id, 'expires_at' => now()->addMinutes(config('adoptions.access_minutes')), 'access_version' => $current->access_version, 'purpose' => 'email_change', 'new_email' => mb_strtolower(trim($email))]);
            app(Outbox::class)->add('guardian.email_change', $grant->id, 'guardian.email_change:'.$grant->id);
            $this->event($current, 'email_change_requested', $current->status, $admin ? 'admin' : 'guardian', $admin?->id);
        });
    }

    public function confirmEmailChange(GuardianAccessGrant $grant, string $token): void
    {
        DB::transaction(function () use ($grant, $token) {
            $adoption = TreeAdoption::findOrFail($grant->tree_adoption_id);
            Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $adoption = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            $grant = GuardianAccessGrant::whereKey($grant->id)->lockForUpdate()->firstOrFail();
            if ($grant->purpose !== 'email_change' || ! $grant->new_email || $adoption->status !== 'active' || $grant->consumed_at || $grant->expires_at->isPast() || $grant->access_version !== $adoption->access_version || ! $grant->token_hash || ! hash_equals($grant->token_hash, hash('sha256', $token))) {
                throw ValidationException::withMessages(['token' => __('public.the_email_change_link_is_invalid_or_has_expired')]);
            }
            $adoption->update(['guardian_email' => $grant->new_email, 'access_version' => $adoption->access_version + 1]);
            $grant->update(['consumed_at' => now(), 'token_hash' => null, 'new_email' => null]);
            $this->event($adoption, 'email_changed', $adoption->status, 'guardian');
        });
    }

    public function cancel(TreeAdoption $adoption): void
    {
        DB::transaction(function () use ($adoption) {
            $tree = Tree::whereKey($adoption->tree_id)->lockForUpdate()->firstOrFail();
            $current = TreeAdoption::whereKey($adoption->id)->lockForUpdate()->firstOrFail();
            abort_unless($current->status === 'active', 403);
            $from = $current->status;
            $current->update(['status' => 'cancelled', 'reserved_tree_id' => null, 'ended_at' => now(), 'access_version' => $current->access_version + 1]);
            if ($tree->adoption_status === 'adopted') {
                $tree->update(['adoption_status' => in_array($tree->physical_condition, ['removed', 'replaced'], true) ? 'unavailable' : 'available']);
            }
            $this->event($current, 'guardian_cancelled', $from, 'guardian');
            app(Outbox::class)->add('adoption.ended', $current->id, 'adoption.ended:'.$current->id);
        });
    }

    public function event(TreeAdoption $adoption, string $event, ?string $from, string $actor, ?int $actorId = null, array $metadata = []): void
    {
        $adoption->events()->create(['event' => $event, 'from_status' => $from, 'to_status' => $adoption->status, 'actor_type' => $actor, 'actor_id' => $actorId, 'metadata' => $metadata]);
    }
}
