<?php

namespace App\Models\Trinity\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Account extends Model
{
    use HasFactory;

    /**
     * The TrinityCore auth database connection
     */
    protected $connection = 'trinity_auth';

    /**
     * Table name
     */
    protected $table = 'account';

    /**
     * Primary key
     */
    protected $primaryKey = 'id';

    /**
     * TrinityCore tables don't use timestamps
     */
    public $timestamps = false;

    /**
     * Mass-assignable fields (safe if you ever seed/test)
     */
    protected $fillable = [
        'username',
        'email',
        'last_ip',
        'last_login',
        'expansion',
        'locked',
    ];

    // Cross-connection relationship to characters DB
    public function characters()
    {
        return $this->hasMany(\App\Models\Trinity\Characters\Character::class, 'account', 'id');
    }

    /**
     * Example: make sure no accidental saves happen unless you intend to
     */
    protected static function booted(): void
    {
        static::saving(fn () => false);
        static::creating(fn () => false);
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }
}
