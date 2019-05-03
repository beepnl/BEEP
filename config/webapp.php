<?php

return [

    'url' 				 => env('WEBAPP_URL', '/webapp#!/'),
    'email_verify_url' 	 => env('WEBAPP_EMAIL_VERIFY_URL', 'login'),
    'password_reset_url' => env('WEBAPP_PASSWORD_RESET_URL', 'login/reset/'),
    
];