<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'action',
        'params',
        'status',
        'attempts',
        'error_text',
        'processed_at',
    ];

    protected $casts = [
        'params' => 'array',
        'processed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    // Status helpers
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isDone(): bool       { return $this->status === 'done'; }
    public function isError(): bool      { return $this->status === 'error'; }

    // Convenience for retries
    public function markProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markDone(): void
    {
        $this->update(['status' => 'done', 'processed_at' => now(), 'error_text' => null]);
    }

    public function markError(string $error): void
    {
        $this->update([
            'status' => 'error',
            'error_text' => $error,
            'attempts' => $this->attempts + 1,
        ]);
    }
}
