<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Auth;
use App\Group;

class GroupInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Group $group, $name, $admin, $token, $invited_by)
    {
        $this->group = $group;
        $this->name  = $name;
        $this->admin = $admin;
        $this->token = $token;
        $this->invited_by = $invited_by;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'emails.group_invitation',
            [
                'group'=>$this->group, 
                'name'=>$this->name, 
                'admin'=>$this->admin, 
                'acceptUrl'=>url('/webapp#!/groups/'.$this->group->id.'/token/'.$this->token), 
                'invited_by'=>$this->invited_by
            ])
            ->replyTo(Auth::user()->email, $this->invited_by)
            ->subject(__('group.subject_invite'));
    }
}
