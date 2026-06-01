<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SupportTicket      $ticket,
        private readonly SupportTicketReply $reply,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isClosed   = in_array($this->ticket->status, ['resolved', 'closed']);
        $statusLine = $isClosed
            ? 'Your ticket has been **' . $this->ticket->status . '**.'
            : 'Your ticket status is now **' . str_replace('_', ' ', $this->ticket->status) . '**.';

        $mail = (new MailMessage)
            ->subject("Re: [{$this->ticket->ticket_number}] {$this->ticket->subject}")
            ->greeting("Hi {$notifiable->name},")
            ->line("The EduBridge support team has replied to your ticket **{$this->ticket->ticket_number}**.")
            ->line("**{$this->ticket->subject}**")
            ->line("---")
            ->line($this->reply->message)
            ->line("---")
            ->line($statusLine);

        if (! $isClosed) {
            $mail->action('View Ticket', route('support.show', $this->ticket));
        } else {
            $mail->line('If you need further help, please open a new support ticket.')
                 ->action('Open New Ticket', route('support.index'));
        }

        return $mail->line('— EduBridge Support Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'support_reply',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'message'       => substr($this->reply->message, 0, 200),
            'status'        => $this->ticket->status,
            'url'           => route('support.show', $this->ticket),
        ];
    }
}
