<?php

namespace App\Models\Trinity\World;

use Illuminate\Database\Eloquent\Model;

class ItemTemplate extends Model
{
    protected $connection = 'trinity_world';
    protected $table = 'item_template';
    protected $primaryKey = 'entry';
    public $timestamps = false;
    protected $guarded = ['*']; // read-only
}
