<?php

namespace App\Notifications;

use App\Models\LiveSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LiveSessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly LiveSession $session) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course       = $this->session->course;
        $scheduledAt  = $this->session->scheduled_at->format('l, d F Y \a\t H:i');
        $duration     = $this->session->duration_minutes . ' minutes';

        return (new MailMessage)
            ->subject("Reminder: Live Class Starting in 30 Minutes — {$this->session->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your live class is starting in **30 minutes**.")
            ->line("**Session:** {$this->session->title}")
            ->line("**Course:** {$course->title}")
            ->line("**Time:** {$scheduledAt}")
            ->line("**Duration:** {$duration}")
            ->action('Join Live Session', $this->session->meeting_url)
            ->line("Make sure you have a stable internet connection and your camera/microphone ready.")
            ->salutation('— The EduBridge Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'live_session_reminder',
            'message'    => "Live class \"{$this->session->title}\" starts in 30 minutes.",
            'session_id' => $this->session->id,
            'url'        => $this->session->meeting_url,
        ];
    }
}
