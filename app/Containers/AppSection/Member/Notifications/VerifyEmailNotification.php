<?php

namespace App\Containers\AppSection\Member\Notifications;

use App\Ship\Parents\Notifications\Notification as ParentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

final class VerifyEmailNotification extends ParentNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $verificationUrl
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = config('member.email.templates.confirm-email.subject', 'Confirm your email');

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.member.confirm-email', [
                'verify_link' => $this->verificationUrl,
                'member_name' => $notifiable->name ?? $notifiable->email,
            ]);
    }
}
