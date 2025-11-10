<?php

namespace App\Models\Trinity\World;

use Illuminate\Database\Eloquent\Model;

class GameTele extends Model
{
    protected $connection = 'trinity_world';
    protected $table = 'game_tele';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = ['*']; // read-only
}
