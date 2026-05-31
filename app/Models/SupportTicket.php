<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'ticket_number', 'subject', 'message',
        'category', 'priority', 'status', 'assigned_to',
        'admin_notes', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, ['resolved', 'closed']);
    }

    public static function generateTicketNumber(): string
    {
        return 'TKT-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'open'        => 'badge-blue',
            'in_progress' => 'badge-amber',
            'waiting'     => 'badge-purple',
            'resolved'    => 'badge-green',
            'closed'      => 'badge-slate',
            default       => 'badge-slate',
        };
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'urgent' => 'badge-red',
            'high'   => 'badge-orange',
            'medium' => 'badge-amber',
            'low'    => 'badge-slate',
            default  => 'badge-slate',
        };
    }
}
