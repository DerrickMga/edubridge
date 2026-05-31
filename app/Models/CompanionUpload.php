<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanionUpload extends Model
{
    protected $fillable = [
        'user_id',
        'conversation_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'type',
        'extracted_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function base64(): string
    {
        return base64_encode(Storage::disk('local')->get($this->stored_path));
    }

    public function url(): string
    {
        return Storage::disk('local')->url($this->stored_path);
    }
}
