<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrinityLink extends Model
{
    protected $fillable = ['user_id','trinity_account_id','trinity_username'];
}
