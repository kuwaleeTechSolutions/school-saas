<?php

namespace App\Mail;

use App\Domain\Tenant\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Notification $notification)
    {
    }

    public function build(): self
    {
        return $this->subject($this->notification->title)
            ->view('emails.notification')
            ->with(['notification' => $this->notification]);
    }
}
