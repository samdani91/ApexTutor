<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number', 'user_id', 'subject', 'description',
        'category', 'priority', 'status', 'assigned_to',
        'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id')
            ->where('is_internal', false)
            ->orderBy('created_at');
    }

    public static function generateTicketNumber(): string
    {
        do {
            $number = 'TKT-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        } while (static::where('ticket_number', $number)->exists());

        return $number;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'account'   => 'Account',
            'technical' => 'Technical',
            'tuition'   => 'Tuition',
            default     => 'Other',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
        };
    }
}
