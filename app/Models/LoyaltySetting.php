<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model
{
    protected $fillable = [
        'target_stickers',
        'reward_type',
        'reward_value',
        'reward_description',
        'album_help_message',
        'redemption_instructions',
        'coupon_code',
        'reward_category',
        'reward_image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'target_stickers' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function active(): self
    {
        return self::where('is_active', true)->latest('id')->firstOrFail();
    }
}
