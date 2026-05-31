<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly User $user) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = ucfirst($this->user->role ?? 'student');

        return (new MailMessage)
            ->subject('Welcome to EduBridge by KMG!')
            ->greeting("Hi {$this->user->name},")
            ->line("Welcome to **EduBridge** — your gateway to world-class online learning.")
            ->line("Your account has been created as a **{$role}**.")
            ->action('Go to Your Dashboard', url('/dashboard'))
            ->line('If you have any questions, reply to this email and our support team will assist you.')
            ->salutation('— The EduBridge Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'welcome',
            'message' => "Welcome to EduBridge, {$this->user->name}!",
            'url'     => '/dashboard',
        ];
    }
}
