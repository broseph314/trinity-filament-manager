<?php

namespace App\Models\Trinity\World;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ItemTemplate extends Model
{
    protected $connection = 'trinity_world';
    protected $table = 'item_template';
    protected $primaryKey = 'entry';
    public $timestamps = false;
    protected $guarded = ['*']; // read-only

    public const QUALITY_LABELS = [
        0=>'Poor',1=>'Common',2=>'Uncommon',3=>'Rare',4=>'Epic',5=>'Legendary',6=>'Artifact',7=>'Heirloom',
    ];

    // Tailwind-ish color keys you can map to badges
    public const QUALITY_COLORS = [
        0=>'gray', 1=>'gray', 2=>'success', 3=>'info', 4=>'purple', 5=>'warning', 6=>'danger', 7=>'primary',
    ];

    public const CLASS_LABELS = [
        0=>'Consumable', 1=>'Container', 2=>'Weapon', 3=>'Gem', 4=>'Armor',
        7=>'Trade Goods', 9=>'Recipe', 12=>'Quest', 13=>'Key', 15=>'Misc',
    ];

    public const SUBCLASS_LABELS = [
        2 => [ // Weapons
            0=>'Axe',1=>'Axe (2H)',2=>'Bow',3=>'Gun',4=>'Mace',5=>'Mace (2H)',
            6=>'Polearm',7=>'Sword',8=>'Sword (2H)',10=>'Staff',13=>'Fist',15=>'Dagger',
            16=>'Thrown',18=>'Crossbow',19=>'Wand',
        ],
        4 => [ // Armor
            0=>'Misc',1=>'Cloth',2=>'Leather',3=>'Mail',4=>'Plate',6=>'Shield',
        ],
    ];

    public const INVENTORY_TYPE_LABELS = [
        0=>'Non-equip',1=>'Head',2=>'Neck',3=>'Shoulder',4=>'Shirt',5=>'Chest',
        6=>'Waist',7=>'Legs',8=>'Feet',9=>'Wrist',10=>'Hands',11=>'Finger',
        12=>'Trinket',13=>'One-Hand',14=>'Shield',15=>'Ranged',16=>'Back',
        17=>'Two-Hand',18=>'Bag',19=>'Tabard',20=>'Robe',21=>'Main Hand',
        22=>'Off Hand',23=>'Holdable',25=>'Thrown',26=>'Ranged (right)',28=>'Relic',
    ];

    /* -------------------- Helpers / accessors -------------------- */

    public function qualityLabel(): string
    {
        return self::QUALITY_LABELS[(int) $this->Quality] ?? (string) $this->Quality;
    }

    public function qualityColor(): string
    {
        return self::QUALITY_COLORS[(int) $this->Quality] ?? 'gray';
    }

    public function classLabel(): string
    {
        return self::CLASS_LABELS[(int) $this->class] ?? (string) $this->class;
    }

    public function subclassLabel(): ?string
    {
        $class = (int) $this->class;
        $sub   = (int) $this->subclass;
        return self::SUBCLASS_LABELS[$class][$sub] ?? null;
    }

    public function inventoryTypeLabel(): string
    {
        return self::INVENTORY_TYPE_LABELS[(int) $this->InventoryType] ?? (string) $this->InventoryType;
    }

    public function displayInfo()
    {
        // item_template.displayid -> item_display_info.ID
        return $this->hasOne(ItemDisplayInfo::class, 'ID', 'displayid');
    }

    /**
     * Returns the icon basename (e.g., "inv_chest_samurai") or null.
     */
    public function inventoryIcon(): ?string
    {
        $icon1 = $this->displayInfo?->InventoryIcon_1 ?? null;
        $icon2 = $this->displayInfo?->InventoryIcon_2 ?? null;
        $raw   = $icon1 ?: $icon2;
        if (!$raw) return null;

        // Normalize: drop any directory + extension, make lowercase
        $name = strtolower($raw);
        $name = preg_replace('~^.*[/\\\\]~', '', $name);   // remove any path like Interface/Icons/
        $name = preg_replace('~\.(blp|png|jpg)$~i', '', $name); // remove extension if present
        return $name ?: null;
    }

    /**
     * Build an icon URL. Sizes: small|medium|large. CDN: evowow|zamimg
     */
    public function iconUrl(string $size = 'large', string $cdn = 'evowow'): ?string
    {
        $size = in_array($size, ['small','medium','large'], true) ? $size : 'large';
        $icon = $this->inventoryIcon();
        if (!$icon) return null;

        return match ($cdn) {
            'evowow' => "https://wotlk.evowow.com/static/images/wow/icons/{$size}/{$icon}.jpg",
            default  => "https://wow.zamimg.com/images/wow/icons/{$size}/{$icon}.jpg",
        };
    }
}
