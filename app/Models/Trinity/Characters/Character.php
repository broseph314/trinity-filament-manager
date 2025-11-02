<?php

namespace App\Models\Trinity\Characters;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $connection = 'trinity_chars';
    protected $table = 'characters';
    protected $primaryKey = 'guid';
    public $timestamps = false;
    public $incrementing = false;

    // === Display accessors used by the table ===

    protected $appends = [
        'class_name', 'race_name', 'gender_name',
        'money_human', 'totaltime_human', 'logout_time_dt',
    ];

    public function getClassNameAttribute(): string
    {
        $map = [
            1 => 'Warrior', 2 => 'Paladin', 3 => 'Hunter', 4 => 'Rogue',
            5 => 'Priest', 6 => 'Death Knight', 7 => 'Shaman', 8 => 'Mage',
            9 => 'Warlock', 11 => 'Druid',
        ];
        return $map[$this->class] ?? (string) $this->class;
    }

    public function getRaceNameAttribute(): string
    {
        $map = [
            1 => 'Human', 2 => 'Orc', 3 => 'Dwarf', 4 => 'Night Elf',
            5 => 'Undead', 6 => 'Tauren', 7 => 'Gnome', 8 => 'Troll',
            10 => 'Blood Elf', 11 => 'Draenei',
        ];
        return $map[$this->race] ?? (string) $this->race;
    }

    public function inventories()
    {
        return $this->hasMany(\App\Models\Trinity\Characters\CharacterInventory::class, 'guid', 'guid');
    }

    public function getGenderNameAttribute(): string
    {
        return (int) $this->gender === 0 ? 'Male' : 'Female';
    }

    public function getMoneyHumanAttribute(): string
    {
        // Trinity stores money in copper
        $copper = (int) $this->money;
        $gold = intdiv($copper, 10000);
        $silver = intdiv($copper % 10000, 100);
        $copper = $copper % 100;
        return sprintf('%dg %ds %dc', $gold, $silver, $copper);
    }

    public function getTotaltimeHumanAttribute(): string
    {
        $s = (int) $this->totaltime;
        $d = intdiv($s, 86400); $s %= 86400;
        $h = intdiv($s, 3600);  $s %= 3600;
        $m = intdiv($s, 60);
        $parts = [];
        if ($d) $parts[] = "{$d}d";
        if ($h) $parts[] = "{$h}h";
        if ($m) $parts[] = "{$m}m";
        return $parts ? implode(' ', $parts) : '0m';
    }

    public function getLogoutTimeDtAttribute(): ?\Illuminate\Support\Carbon
    {
        // Many branches store UNIX seconds; adjust if yours is already a datetime
        $t = (int) $this->logout_time;
        return $t > 0 ? \Illuminate\Support\Carbon::createFromTimestamp($t) : null;
    }

    // Relationship back to Account if you need it
    public function account()
    {
        return $this->belongsTo(\App\Models\Trinity\Auth\Account::class, 'account', 'id');
    }

    protected static function booted(): void
    {
        // protect core tables (optional)
        static::saving(fn() => false);
        static::creating(fn() => false);
        static::updating(fn() => false);
        static::deleting(fn() => false);
    }

}
