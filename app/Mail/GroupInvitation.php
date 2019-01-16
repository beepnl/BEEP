<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Group;

class GroupInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Group $group, $admin, $token)
    {
        $this->group = $group;
        $this->admin = $admin;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.group_invitation',['group'=>$this->group, 'admin'=>$this->admin, 'acceptUrl'=>url('/webapp/index.html#!/groups/'.$this->group->id.'/token/'.$this->token)]);
    }
}
