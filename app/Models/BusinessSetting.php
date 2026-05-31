<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BusinessSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    // Cache key prefix
    const CACHE_PREFIX = 'business_setting_';

    // Scopes
    public function scopeByKey($query, $key)
    {
        return $query->where('key', $key);
    }

    // Static methods for easy access
    public static function get($key, $default = null, $useCache = true)
    {
        if (!$useCache) {
            $setting = self::where('key', $key)->first();
            return $setting ? self::castValue($setting->value, $setting->type) : $default;
        }

        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, 60, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? self::castValue($setting->value, $setting->type) : $default;
        });
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        // Convert value based on type before saving
        $processedValue = $value;
        if ($type === 'json' && is_array($value)) {
            $processedValue = json_encode($value);
        } elseif ($type === 'boolean') {
            // Normalizar booleanos para evitar interpretar strings como true
            $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $processedValue = $bool ? 1 : 0;
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $processedValue,
                'type' => $type,
                'description' => $description,
            ]
        );

        // Clear individual and batch cache
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget('business_settings_all');

        // Clear Laravel config cache to ensure changes are reflected immediately
        if (app()->configurationIsCached()) {
            \Artisan::call('config:clear');
        }

        return $setting;
    }

    public static function castValue($value, $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value
        };
    }

    // Accessor
    public function getCastedValueAttribute()
    {
        return self::castValue($this->value, $this->type);
    }

    // Mutator
    public function setValueAttribute($value)
    {
        if ($this->type === 'json') {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
