<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'character_guid',
        'character_name',
        'category',
        'status',
        'reject_reason',
        'meta',
        'approved_at',
        'processed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lines()
    {
        return $this->hasMany(RequestLine::class);
    }

    // Helpers / scopes
    public function scopePending($q)     { return $q->where('status', 'pending'); }
    public function scopeApproved($q)    { return $q->where('status', 'approved'); }
    public function scopeRejected($q)    { return $q->where('status', 'rejected'); }
    public function scopeProcessing($q)  { return $q->where('status', 'processing'); }
    public function scopeDone($q)        { return $q->where('status', 'done'); }
    public function scopeFailed($q)      { return $q->where('status', 'failed'); }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }

    public function markApproved(?string $note = null): void
    {
        $this->forceFill([
            'status' => 'approved',
            'approved_at' => now(),
        ])->save();

        // optionally append admin note to meta
        if ($note) {
            $meta = $this->meta ?? [];
            $meta['admin_notes'][] = ['time' => now(), 'note' => $note];
            $this->update(['meta' => $meta]);
        }
    }

    public function markRejected(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'reject_reason' => $reason,
        ]);
    }

    public function markDone(): void
    {
        $this->update([
            'status' => 'done',
            'processed_at' => now(),
        ]);
    }

    public function markFailed(?string $error = null): void
    {
        $meta = $this->meta ?? [];
        if ($error) {
            $meta['errors'][] = ['time' => now(), 'error' => $error];
        }

        $this->update([
            'status' => 'failed',
            'meta' => $meta,
        ]);
    }
}
