<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Auth;
use App\Models\Alert;

class AlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Alert $alert, $name, $last_values_string, $display_date_local)
    {
        $this->alert = $alert;
        $this->name  = $name; 
        $this->last_values_string  = $last_values_string; 
        $this->display_date_local  = $display_date_local; 
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = __('alert.subject').(null !== $this->alert->alert_rule_name ? ' - '.$this->alert->alert_rule_name : '').(null !== $this->alert->hive_name ? ' - '.__('beep.Hive').': '.$this->alert->hive_name : '');
        return $this->markdown(
            'emails.alert',
            [
                'alert'=>$this->alert, 
                'name' =>$this->name, 
                'last_values_string' =>$this->last_values_string, 
                'display_date_local' =>$this->display_date_local, 
                'url'  =>env('WEBAPP_URL').'alerts',
                'url_settings' =>env('WEBAPP_URL').'alertrules'
            ])
            ->subject($subject);
    }
}
