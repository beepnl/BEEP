<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Auth;
use App\Alert;

class AlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Group $alert, $name)
    {
        $this->alert = $alert;
        $this->name  = $name; 
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'emails.alert',
            [
                'alert'=>$this->alert, 
                'name' =>$this->name, 
                'url'  =>env('WEBAPP_URL').'alerts'
            ])
            ->subject(__('alert.subject').(isset($this->alert->hive_name) ? ' '.__('beep.Hive').': '.$this->alert->hive_name : ''));
    }
}
