<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppFlow extends Model
{
    protected $table = 'whatsapp_flows';

    protected $fillable = [
        'flow_key',
        'meta_flow_id',
        'name',
        'status',
        'categories',
        'endpoint_uri',
    ];

    protected $casts = [
        'categories' => 'array',
    ];

    public static function findByKey(string $key): ?self
    {
        return static::where('flow_key', $key)->first();
    }

    public function isPublished(): bool
    {
        return $this->status === 'PUBLISHED';
    }
}
