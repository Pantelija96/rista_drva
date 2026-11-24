<?php

return [
    'show_payment_instructions' => (bool) env('ECOSYNC_SHOW_PAYMENT_INSTRUCTIONS', false),
    'notification_email' => env('ECOSYNC_NOTIFICATION_EMAIL', 'eoecosync@gmail.com'),
    'notification_name' => env('ECOSYNC_NOTIFICATION_NAME', 'EcoSync'),
    'mail_use_queue'=>env('ECOSYNC_MAIL_USE_QUEUE',false),
    'mail_max_attempts' => 5,
];
