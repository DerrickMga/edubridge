<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Announcement $announcement) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course      = $this->announcement->course;
        $authorName  = $this->announcement->author->name ?? 'Your Instructor';

        return (new MailMessage)
            ->subject("New Announcement: {$this->announcement->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("{$authorName} has posted a new announcement in **{$course->title}**.")
            ->line("**{$this->announcement->title}**")
            ->line($this->announcement->body)
            ->action('View Announcement', url("/courses/{$course->id}#announcements"))
            ->salutation('— The EduBridge Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'announcement',
            'message'         => "New announcement in {$this->announcement->course->title}: {$this->announcement->title}",
            'announcement_id' => $this->announcement->id,
            'course_id'       => $this->announcement->course_id,
            'url'             => "/courses/{$this->announcement->course_id}#announcements",
        ];
    }
}
