<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course  = $this->assignment->course;
        $dueAt   = $this->assignment->due_at->format('l, d F Y \a\t H:i');
        $hoursLeft = (int) now()->diffInHours($this->assignment->due_at, false);

        $urgency = $hoursLeft <= 6 ? '⚠ Due very soon' : 'Due in ' . $hoursLeft . ' hours';

        return (new MailMessage)
            ->subject("{$urgency}: {$this->assignment->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("This is a reminder that your assignment is due soon.")
            ->line("**Assignment:** {$this->assignment->title}")
            ->line("**Course:** {$course->title}")
            ->line("**Due:** {$dueAt}")
            ->action('Submit Assignment', url("/courses/{$course->id}/assignments/{$this->assignment->id}"))
            ->line("Don't leave it to the last minute — submit early to avoid technical issues.")
            ->salutation('— The EduBridge Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'assignment_due',
            'message'       => "Assignment \"{$this->assignment->title}\" is due soon.",
            'assignment_id' => $this->assignment->id,
            'course_id'     => $this->assignment->course_id,
            'url'           => "/courses/{$this->assignment->course_id}/assignments/{$this->assignment->id}",
        ];
    }
}
