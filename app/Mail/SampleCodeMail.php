<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Auth;
use App\Models\Alert;

class SampleCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $code, $hive, $link)
    {
        $this->name  = $name; 
        $this->code  = $code;
        $this->hive  = $hive; 
        $this->link  = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = __('samplecode.subject').' ('.$this->code.')'.(null !== $this->hive ? ' - '.__('samplecode.Hive', ['hive'=>$this->hive]) : '');
        return $this->markdown(
            'emails.samplecode',
            [
                'code'=>$this->code, 
                'name' =>$this->name, 
                'hive' =>$this->hive, 
                'link' =>env('WEBAPP_URL').$this->link,
            ])
            ->subject($subject);
    }
}
