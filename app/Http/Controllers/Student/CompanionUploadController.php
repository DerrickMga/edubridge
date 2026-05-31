<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CompanionUpload;
use App\Models\Conversation;
use App\Services\GptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanionUploadController extends Controller
{
    public function __construct(private GptService $gpt) {}

    /**
     * Upload a file (image or PDF), extract/analyse it, and return AI answer.
     */
    public function store(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $request->validate([
            'file'     => [
                'required',
                'file',
                'max:10240',  // 10 MB
                'mimes:jpg,jpeg,png,gif,webp,pdf',
            ],
            'question' => 'nullable|string|max:2000',
        ]);

        $file     = $request->file('file');
        $mime     = $file->getMimeType();
        $origName = $file->getClientOriginalName();
        $isImage  = str_starts_with($mime, 'image/');
        $isPdf    = $mime === 'application/pdf';

        // Store securely — private disk, keyed by user
        $dir   = 'companion-uploads/' . $request->user()->id;
        $name  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path  = $file->storeAs($dir, $name, 'local');

        $question      = $request->input('question', 'Please explain this and help me understand it.');
        $extractedText = null;
        $aiContent     = '';

        try {
            if ($isImage) {
                // GPT-4o vision
                $base64    = base64_encode(file_get_contents($file->getRealPath()));
                $system    = $this->buildSystem($conversation);
                $aiContent = $this->gpt->vision($base64, $mime, $question, $system);

            } elseif ($isPdf) {
                // Extract text from PDF via raw stream reading
                $rawPdf        = file_get_contents($file->getRealPath());
                $extractedText = $this->extractPdfText($rawPdf);
                $context       = $extractedText ?: '[PDF text could not be extracted automatically]';
                $system        = $this->buildSystem($conversation);
                $prompt        = "I've uploaded a PDF document. Here is the extracted text:\n\n"
                               . substr($context, 0, 8000)
                               . "\n\n---\n\nMy question: " . $question;
                $aiContent     = $this->gpt->chat([['role' => 'user', 'content' => $prompt]], $system);

            } else {
                $aiContent = 'Unsupported file type. Please upload an image (JPG, PNG, GIF, WEBP) or a PDF.';
            }
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            return response()->json(['error' => 'AI analysis failed: ' . $e->getMessage()], 500);
        }

        // Persist the upload record
        $upload = CompanionUpload::create([
            'user_id'         => $request->user()->id,
            'conversation_id' => $conversation->id,
            'original_name'   => $origName,
            'stored_path'     => $path,
            'mime_type'       => $mime,
            'file_size'       => $file->getSize(),
            'type'            => $isImage ? 'image' : ($isPdf ? 'pdf' : 'other'),
            'extracted_text'  => $extractedText,
        ]);

        // Save messages so the conversation history is updated
        $conversation->messages()->create([
            'role'    => 'user',
            'content' => "[Uploaded file: {$origName}] " . $question,
        ]);

        $message = $conversation->messages()->create([
            'role'       => 'assistant',
            'model_used' => 'gpt',
            'content'    => $aiContent,
            'metadata'   => ['upload_id' => $upload->id, 'file_name' => $origName],
        ]);

        $conversation->touch();

        return response()->json([
            'message'     => $message,
            'upload_id'   => $upload->id,
            'file_name'   => $origName,
            'file_type'   => $isImage ? 'image' : 'pdf',
            'model_used'  => 'gpt',
        ]);
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    private function buildSystem(Conversation $conversation): string
    {
        $subject = $conversation->subject ?? 'general studies';
        $level   = $conversation->level   ?? 'secondary school';
        return "You are an expert tutor for {$subject} at {$level} level in Zimbabwe. "
             . "Analyse the student's uploaded content carefully and provide a thorough, "
             . "educational explanation. Use markdown formatting with headings and bullet points. "
             . "Show all working steps clearly. Be encouraging and pedagogical.";
    }

    /**
     * Very lightweight PDF text extractor.
     * Reads raw PDF stream content markers without any library dependency.
     */
    private function extractPdfText(string $rawPdf): string
    {
        $text = '';

        // Attempt 1: extract BT...ET text blocks (standard PDF text operators)
        if (preg_match_all('/BT(.+?)ET/s', $rawPdf, $matches)) {
            foreach ($matches[1] as $block) {
                // Extract string literals: (text) and <hex>
                if (preg_match_all('/\(([^)\\\\]*(?:\\\\.[^)\\\\]*)*)\)/', $block, $strings)) {
                    foreach ($strings[1] as $s) {
                        $decoded = stripcslashes($s);
                        if (mb_detect_encoding($decoded, 'UTF-8', true)) {
                            $text .= ' ' . $decoded;
                        }
                    }
                }
            }
        }

        // Attempt 2: fallback — extract all printable ASCII runs
        if (strlen(trim($text)) < 50) {
            preg_match_all('/[\x20-\x7E\n\r\t]{4,}/', $rawPdf, $runs);
            $text = implode(' ', array_filter($runs[0], fn($s) => strlen(trim($s)) > 3));
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
