<?php

namespace App\Models\Trinity\Characters;

use Illuminate\Database\Eloquent\Model;

class ItemInstance extends Model
{
    protected $connection = 'trinity_chars';
    protected $table = 'item_instance';
    protected $primaryKey = 'guid';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = ['*']; // read-only

    protected static function booted(): void
    {
        // protect core tables (optional)
        static::saving(fn() => false);
        static::creating(fn() => false);
        static::updating(fn() => false);
        static::deleting(fn() => false);
    }
}
