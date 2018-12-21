<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use URL;
use Carbon\Carbon;

class VerifyEmail extends VerifyEmailBase
{
//    use Queueable;

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $prefix = config('emailverification.url') . config('emailverification.email_verify_url');
        $temporarySignedURL = URL::temporarySignedRoute(
            'apiverification.verify', Carbon::now()->addMinutes(60), ['id' => $notifiable->getKey()]
        );

        // I use urlencode to pass a link to my frontend.
        return $prefix . $temporarySignedURL;
    }
}