<?php

return [
    'url' => env('WEBAPP_URL', 'APP_URL'),
    // path to frontend page with query param queryURL(temporarySignedRoute URL)
    'email_verify_url' => env('WEBAPP_EMAIL_VERIFY_URL', '/verify-email?queryURL='),
];