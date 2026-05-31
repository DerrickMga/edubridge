<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Course $course,
        private readonly Payment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amountFormatted = $this->payment->currency === 'USD'
            ? '$' . number_format($this->payment->amount, 2)
            : 'ZWG ' . number_format($this->payment->amount, 2);

        return (new MailMessage)
            ->subject("Enrollment Confirmed: {$this->course->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your enrollment in **{$this->course->title}** has been confirmed.")
            ->line("**Payment received:** {$amountFormatted} via " . ucfirst($this->payment->provider))
            ->line("**Reference:** {$this->payment->provider_reference}")
            ->action('Start Learning Now', url("/courses/{$this->course->id}"))
            ->line('We hope you enjoy the course. Happy learning!')
            ->salutation('— The EduBridge Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'enrollment',
            'message'   => "You are now enrolled in {$this->course->title}.",
            'course_id' => $this->course->id,
            'url'       => "/courses/{$this->course->id}",
        ];
    }
}
