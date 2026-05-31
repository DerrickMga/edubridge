<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateEarnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Certificate $certificate,
        private readonly Course $course
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Certificate Earned: {$this->course->title}")
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line("You have successfully completed **{$this->course->title}** and earned your certificate.")
            ->line("**Certificate Number:** {$this->certificate->certificate_number}")
            ->line("**Issued on:** " . $this->certificate->issued_at->format('d F Y'))
            ->action('View Your Certificate', url("/certificates/{$this->certificate->id}"))
            ->line('Well done on completing the course. Keep learning and growing!')
            ->salutation('— The EduBridge Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'certificate',
            'message'        => "You earned a certificate for {$this->course->title}!",
            'certificate_id' => $this->certificate->id,
            'course_id'      => $this->course->id,
            'url'            => "/certificates/{$this->certificate->id}",
        ];
    }
}
