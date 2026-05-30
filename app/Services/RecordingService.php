<?php
namespace App\Services;

use App\Models\{LiveSession, Recording};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecordingService
{
    /**
     * Store an uploaded recording file and create the DB record.
     */
    public function storeUpload(LiveSession $session, UploadedFile $file, string $title): Recording
    {
        $path = $file->storeAs(
            "recordings/{$session->course_id}/{$session->id}",
            Str::slug($title).'-'.time().'.'.$file->getClientOriginalExtension(),
            's3'
        );

        return Recording::create([
            'live_session_id'  => $session->id,
            'course_id'        => $session->course_id,
            'teacher_id'       => $session->teacher_id,
            'title'            => $title,
            'storage_path'     => $path,
            'source'           => 'upload',
            'file_size_bytes'  => $file->getSize(),
            'status'           => 'available',
            'is_public'        => true,
        ]);
    }

    /**
     * Link an external recording URL (Zoom cloud, YouTube, etc.)
     */
    public function linkExternal(LiveSession $session, string $url, string $title, string $source = 'zoom'): Recording
    {
        return Recording::create([
            'live_session_id' => $session->id,
            'course_id'       => $session->course_id,
            'teacher_id'      => $session->teacher_id,
            'title'           => $title,
            'external_url'    => $url,
            'source'          => $source,
            'status'          => 'available',
            'is_public'       => true,
        ]);
    }

    /**
     * Delete a recording and its stored file.
     */
    public function delete(Recording $recording): void
    {
        if ($recording->storage_path) {
            Storage::disk('s3')->delete($recording->storage_path);
        }
        $recording->delete();
    }
}
