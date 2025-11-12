<?php

namespace App\Models\Trinity\World;

use Illuminate\Database\Eloquent\Model;

class ItemDisplayInfo extends Model
{
    protected $connection = 'trinity_world';
    protected $table      = 'item_display_info';
    protected $primaryKey = 'ID';     // matches your dump
    public $incrementing  = false;
    public $timestamps    = false;
    protected $guarded    = ['*'];
}
