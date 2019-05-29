<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;

class GroupAcceptation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $group, $user)
    {
        $this->name  = $name;
        $this->group = $group;
        $this->user  = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'emails.group_acceptation',
            [
                'group'=>$this->group, 
                'name'=>$this->name, 
                'user'=>$this->user
            ]
        )
        ->subject(__('group.subject_accept'));
    }
}
