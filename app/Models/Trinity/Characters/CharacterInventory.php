<?php

namespace App\Models\Trinity\Characters;

use Illuminate\Database\Eloquent\Model;

class CharacterInventory extends Model
{
    protected $connection = 'trinity_chars';
    protected $table = 'character_inventory';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = ['*']; // read-only
    protected $with = ['itemInstance'];
    protected $primaryKey = 'item';
    protected $keyType = 'int';


    // Relationships inside the same (characters) DB
    public function itemInstance()
    {
        return $this->belongsTo(ItemInstance::class, 'item', 'guid');
    }

    // Convenience “computed” accessors for table columns
    protected $appends = ['item_entry', 'item_count', 'bag_label', 'slot_label', 'item_name', 'quality_name', 'quality_color'];

    public function getItemEntryAttribute(): ?int
    {
        return optional($this->itemInstance)->itemEntry;
    }

    public function getItemCountAttribute(): ?int
    {
        return optional($this->itemInstance)->count;
    }

    public function getBagLabelAttribute(): string
    {
        return (string) ($this->bag == 0 ? 'Backpack' : "Bag {$this->bag}");
    }

    public function getSlotLabelAttribute(): string
    {
        return (string) $this->slot;
    }

    public function getItemNameAttribute(): string
    {
        return \App\Services\ItemTemplateLookup::name((int) ($this->item_entry ?? 0)) ?? ('#'.$this->item_entry);
    }

    public function getQualityNameAttribute(): ?string
    {
        return \App\Services\ItemTemplateLookup::qualityName((int) ($this->item_entry ?? 0));
    }

    public function getQualityColorAttribute(): string
    {
        return \App\Services\ItemTemplateLookup::qualityColor((int) ($this->item_entry ?? 0));
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
