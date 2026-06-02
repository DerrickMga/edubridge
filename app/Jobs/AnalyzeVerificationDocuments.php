<?php

namespace App\Jobs;

use App\Models\TeacherVerification;
use App\Services\GptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalyzeVerificationDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $verificationId) {}

    public function handle(GptService $gpt): void
    {
        $verification = TeacherVerification::find($this->verificationId);

        if (! $verification) {
            return;
        }

        // Need at least the ID front; selfie is the primary face-match image
        if (! $verification->id_document_front) {
            return;
        }

        try {
            $result = $this->runAnalysis($gpt, $verification);

            $verification->update([
                'ai_check_result' => $result,
                'ai_checked_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AnalyzeVerificationDocuments failed', [
                'verification_id' => $this->verificationId,
                'error'           => $e->getMessage(),
            ]);

            $verification->update([
                'ai_check_result' => [
                    'error'   => $e->getMessage(),
                    'status'  => 'error',
                ],
                'ai_checked_at' => now(),
            ]);
        }
    }

    private function runAnalysis(GptService $gpt, TeacherVerification $verification): array
    {
        $images = $this->loadImages($verification);

        if (empty($images)) {
            return ['status' => 'skipped', 'reason' => 'No images could be loaded from storage.'];
        }

        // Guard: id_document_front may be absent when teacher uploaded a PDF
        // (PDFs are skipped by loadImages as they cannot be sent to vision AI).
        if (! isset($images['id_document_front'])) {
            return [
                'status' => 'skipped',
                'reason' => 'ID document was uploaded as a PDF and cannot be processed by vision AI. Ask the teacher to re-upload as JPG or PNG.',
            ];
        }

        $hasSelfie = isset($images['selfie_with_id']);

        // Build the message content — always include the ID front;
        // add selfie if available for face-matching.
        $content = [];

        if ($hasSelfie) {
            $content[] = [
                'type' => 'text',
                'text' => 'You are a KYC document analyst. You have been given two images: the first is the ID/passport document, the second is a selfie of the person holding their ID. Analyse both images and return a JSON object (no markdown, raw JSON only) with the following fields: "name_on_id" (string or null), "id_number_on_id" (string or null), "document_type" (e.g. passport, national_id, driving_licence), "face_on_id_detected" (boolean), "face_on_selfie_detected" (boolean), "faces_match" (boolean — true if both faces appear to be the same person, false if they do not, null if you cannot determine), "confidence" (integer 0–100 representing your confidence in the face match), "name_matches_submitted" (boolean or null — compare name_on_id to the submitted name "' . addslashes($verification->full_legal_name) . '"), "id_number_matches_submitted" (boolean or null — compare id_number_on_id to the submitted number "' . addslashes($verification->national_id_number) . '"), "flags" (array of short strings describing any concerns, empty array if none), "summary" (one sentence summary of your findings).',
            ];
        } else {
            $content[] = [
                'type' => 'text',
                'text' => 'You are a KYC document analyst. You have been given one image: an ID/passport document. Analyse it and return a JSON object (no markdown, raw JSON only) with the following fields: "name_on_id" (string or null), "id_number_on_id" (string or null), "document_type" (e.g. passport, national_id, driving_licence), "face_on_id_detected" (boolean), "face_on_selfie_detected" (null — no selfie provided), "faces_match" (null — no selfie to compare), "confidence" (null), "name_matches_submitted" (boolean or null — compare name_on_id to the submitted name "' . addslashes($verification->full_legal_name) . '"), "id_number_matches_submitted" (boolean or null — compare id_number_on_id to the submitted number "' . addslashes($verification->national_id_number) . '"), "flags" (array of short strings describing any concerns, empty array if none), "summary" (one sentence summary of your findings).',
            ];
        }

        // Attach ID document front
        $content[] = [
            'type'      => 'image_url',
            'image_url' => ['url' => 'data:' . $images['id_document_front']['mime'] . ';base64,' . $images['id_document_front']['base64']],
        ];

        // Attach selfie if available
        if ($hasSelfie) {
            $content[] = [
                'type'      => 'image_url',
                'image_url' => ['url' => 'data:' . $images['selfie_with_id']['mime'] . ';base64,' . $images['selfie_with_id']['base64']],
            ];
        }

        $messages = [['role' => 'user', 'content' => $content]];

        $raw = $gpt->chatWithMessages($messages);

        // Strip markdown code fences if present
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/i', '', $raw);

        $parsed = json_decode($raw, true);

        if (! is_array($parsed)) {
            return ['status' => 'error', 'reason' => 'GPT-4o returned non-JSON response.', 'raw' => substr($raw, 0, 500)];
        }

        $parsed['status'] = 'completed';
        return $parsed;
    }

    /**
     * Load a field image from private storage as base64.
     * Returns ['base64' => ..., 'mime' => ...] or null on failure.
     */
    private function loadImages(TeacherVerification $verification): array
    {
        $out = [];

        foreach (['id_document_front', 'selfie_with_id'] as $field) {
            $path = $verification->{$field};
            if (! $path) {
                continue;
            }

            if (! Storage::disk('private')->exists($path)) {
                continue;
            }

            $bytes = Storage::disk('private')->get($path);
            if (! $bytes) {
                continue;
            }

            // Determine MIME from extension (DomPDF approach — no finfo needed)
            $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'gif'         => 'image/gif',
                'webp'        => 'image/webp',
                default       => 'image/jpeg',
            };

            // PDFs cannot be sent as vision images — skip
            if ($ext === 'pdf') {
                continue;
            }

            // GPT-4o vision has a ~20 MB base64 limit per image; skip oversized
            if (strlen($bytes) > 15_000_000) {
                continue;
            }

            $out[$field] = ['base64' => base64_encode($bytes), 'mime' => $mime];
        }

        return $out;
    }
}
