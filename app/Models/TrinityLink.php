<?php

namespace App\Models;

use App\Models\Trinity\Auth\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrinityLink extends Model
{
    protected $fillable = [
        'user_id',
        'trinity_account_id',
        'trinity_username'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        // Works across connections because TcAccount defines its own connection
        return $this->belongsTo(Account::class, 'trinity_account_id', 'id');
    }
}
