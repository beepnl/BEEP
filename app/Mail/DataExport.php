<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DataExport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $attachment_disk, $attachment_path)
    {
        $this->user = $user;
        $this->attachment_disk = $attachment_disk;
        $this->attachment_path = $attachment_path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->markdown('emails.export', ['name' => $this->user->name])->attachFromStorageDisk($this->attachment_disk, $this->attachment_path);
    }
}
