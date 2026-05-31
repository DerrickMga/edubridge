<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Models\Recording;
use App\Services\YouTubeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UploadRecordingToYouTubeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60 * 60; // 1 hour per video
    public int $tries   = 2;

    public function __construct(public int $recordingId)
    {
    }

    public function handle(YouTubeService $youtube): void
    {
        /** @var Recording|null $recording */
        $recording = Recording::with(['course', 'lesson', 'liveSession', 'teacher'])->find($this->recordingId);
        if (! $recording) {
            return;
        }

        if ($recording->hasYouTube()) {
            return; // already uploaded
        }

        $recording->update(['youtube_status' => 'uploading', 'youtube_error' => null]);

        try {
            $result = $youtube->uploadRecording($recording);

            $recording->update([
                'youtube_video_id' => $result['video_id'],
                'youtube_url'      => $result['url'],
                'youtube_status'   => 'uploaded',
                'youtube_error'    => null,
                'source'           => 'youtube',
            ]);

            // Deep-link: if this recording is attached to a lesson and the
            // lesson has no video yet, attach the YouTube ID so students
            // see it embedded on the lesson page.
            if ($recording->lesson_id) {
                /** @var Lesson $lesson */
                $lesson = $recording->lesson;
                if ($lesson && empty($lesson->youtube_video_id) && empty($lesson->video_url)) {
                    $lesson->update([
                        'youtube_video_id' => $result['video_id'],
                        'video_url'        => $result['url'],
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('YouTube upload failed', [
                'recording_id' => $recording->id,
                'error'        => $e->getMessage(),
            ]);
            $recording->update([
                'youtube_status' => 'failed',
                'youtube_error'  => mb_substr($e->getMessage(), 0, 1000),
            ]);
            throw $e;
        }
    }
}
