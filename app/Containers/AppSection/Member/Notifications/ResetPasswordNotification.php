<?php

namespace App\Containers\AppSection\Member\Notifications;

use App\Ship\Parents\Notifications\Notification as ParentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

final class ResetPasswordNotification extends ParentNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly ?string $resetUrl
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = config('member.email.templates.password-reminder.subject', 'Reset your password');

        return (new MailMessage())
            ->subject($subject)
            ->view('emails.member.password-reminder', [
                'reset_link' => $this->resetUrl,
                'token' => $this->token,
            ]);
    }
}
