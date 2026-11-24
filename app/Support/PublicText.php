<?php
namespace App\Support;

/** Display-only labels for persisted enum values; never translate personal names. */
final class PublicText
{
    public static function label(?string $text): string
    {
        $key = self::KEYS[$text ?? ''] ?? null;
        return $key ? __($key) : ($text ?? '');
    }

    private const KEYS = [
        'Aktivna briga o stablu' => 'public.active_guardianship',
        'Aktivno' => 'public.active',
        'Anonimno' => 'public.anonymous',
        'Dostupno' => 'public.available',
        'Druga obrazovna ustanova' => 'public.other_educational_institution',
        'Drugo' => 'public.other',
        'Inicijali' => 'public.initials',
        'Isteklo' => 'public.expired',
        'Jedno stablo po maturantu' => 'public.one_tree_per_graduate',
        'Na čekanju' => 'public.pending',
        'Nedostupno' => 'public.unavailable',
        'Odbijeno' => 'public.rejected',
        'Odobreno' => 'public.approved',
        'Orezivanje' => 'public.pruning',
        'Osnovna škola' => 'public.primary_school',
        'Otkazano' => 'public.cancelled',
        'Oštećeno' => 'public.damaged',
        'Planirano' => 'public.planned',
        'Posađeno' => 'public.planted',
        'Potrebna nega' => 'public.needs_care',
        'Pregled' => 'public.inspection',
        'Prihrana' => 'public.fertilizing',
        'Prijava problema' => 'public.issue_report',
        'Puno ime' => 'public.full_name_3',
        'Simbolično stablo generacije' => 'public.symbolic_tree_for_a_graduating_class',
        'Simbolično usvajanje' => 'public.symbolic_adoption',
        'Srednja škola' => 'public.secondary_school',
        'U razmatranju' => 'public.under_review',
        'Usvojeno' => 'public.adopted',
        'Zalivanje' => 'public.watering',
        'Završeno' => 'public.ended',
        'Čeka email potvrdu' => 'public.awaiting_email_verification',
        'Čeka odobrenje' => 'public.awaiting_approval',
        'Čišćenje' => 'public.cleaning',
        'Zamenjeno' => 'public.replaced',
        'Uklonjeno' => 'public.removed',
    ];
}
