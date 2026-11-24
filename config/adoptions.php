<?php

return [
    'verification_minutes' => (int) env('ADOPTION_VERIFICATION_EXPIRES_MINUTES', 60),
    'reservation_minutes' => (int) env('ADOPTION_RESERVATION_EXPIRES_MINUTES', 60),
    'review_hours' => (int) env('ADOPTION_REVIEW_EXPIRES_HOURS', 168),
    'access_minutes' => (int) env('GUARDIAN_ACCESS_EXPIRES_MINUTES', 20),
    'consent_version' => '2026-11-v1',
    // The first release requires administrative approval after verification.
    'require_admin_approval' => true,
];
