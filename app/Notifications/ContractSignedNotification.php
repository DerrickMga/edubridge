<?php

namespace App\Notifications;

use App\Models\TeacherContract;
use App\Models\TeacherPolicy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractSignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TeacherContract $contract) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contract  = $this->contract->load('teacher');
        $signedAt  = $contract->signed_at?->format('d M Y, H:i') . ' UTC';
        $expiresAt = optional($contract->expires_at)->format('d M Y') ?? 'N/A';
        $ref       = 'EDUBR-CONTRACT-' . str_pad($contract->id, 6, '0', STR_PAD_LEFT) . '-V' . $contract->version;

        $pdf = $this->buildPdf($contract);

        return (new MailMessage)
            ->subject("Your EduBridge Teaching Contract — Signed ({$ref})")
            ->greeting("Hi {$contract->teacher->name},")
            ->line('Thank you for signing your EduBridge Teaching Contract. Please find your signed copy attached to this email.')
            ->line("**Signed:** {$signedAt}")
            ->line("**Signed by:** {$contract->signed_name}")
            ->line("**From IP:** {$contract->signed_ip}")
            ->line("**Expires:** {$expiresAt}")
            ->line("**Reference:** {$ref}")
            ->action('View Contract Online', route('teacher.policies.contract'))
            ->line('Keep this email as your record. If you did not sign this contract, please contact us immediately at legal@kmgvitallinks.co.uk.')
            ->salutation('— The EduBridge Team, KMG Vital Links (Pvt) Ltd')
            ->attachData($pdf->output(), "{$ref}.pdf", ['mime' => 'application/pdf']);
    }

    private function buildPdf(TeacherContract $contract): \Barryvdh\DomPDF\PDF
    {
        // Load full policy bodies for the snapshot entries
        $policyIds = collect($contract->terms_snapshot ?? [])->pluck('policy_id')->filter()->values();
        $policies  = TeacherPolicy::whereIn('id', $policyIds)->orderBy('category')->orderBy('slug')->get();

        return Pdf::loadView('pdf.contract', compact('contract', 'policies'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');
    }
}
