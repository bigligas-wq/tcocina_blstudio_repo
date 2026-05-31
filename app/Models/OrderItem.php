<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'selected_variants',
        'selected_options',
        'configuration_data',
        'special_instructions',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'selected_variants' => 'array',
            'selected_options' => 'array',
            'configuration_data' => 'array',
        ];
    }

    protected $appends = ['configuration_text'];

    // Relaciones
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFormattedUnitPriceAttribute(): string
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getProductNameWithVariantsAttribute(): string
    {
        $name = $this->product->name;

        if ($this->selected_variants && is_array($this->selected_variants)) {
            $variants = collect($this->selected_variants)->pluck('value')->join(', ');
            $name .= " ({$variants})";
        }

        return $name;
    }

    public function getSelectedOptionsTextAttribute(): string
    {
        if (!$this->selected_options || !is_array($this->selected_options)) {
            return '';
        }

        return collect($this->selected_options)->pluck('value')->join(', ');
    }

    public function getSelectedDipsTextAttribute(): string
    {
        if (!$this->selected_options || !is_array($this->selected_options)) {
            return '';
        }

        $dips = collect($this->selected_options)
            ->filter(function ($option) {
                return isset($option['name']) && $option['name'] === 'Dip';
            })
            ->pluck('value')
            ->join(', ');

        return $dips;
    }

    public function getSelectedAderezosTextAttribute(): string
    {
        if (!$this->selected_options || !is_array($this->selected_options)) {
            return '';
        }

        $aderezos = collect($this->selected_options)
            ->filter(function ($option) {
                return isset($option['name']) && $option['name'] === 'Aderezos';
            })
            ->pluck('value')
            ->join(', ');

        return $aderezos;
    }

    // Métodos para la nueva estructura de configuration_data
    public function getConfigurationAttribute(): array
    {
        return $this->configuration_data ?? [];
    }

    public function getMedallonesAttribute(): string
    {
        return $this->configuration['medallones'] ?? '';
    }

    public function getTipoMedallonAttribute(): string
    {
        return $this->configuration['tipo_medallon'] ?? '';
    }

    public function getAderezosAttribute(): array
    {
        return $this->configuration['aderezos'] ?? [];
    }

    public function getExtrasAttribute(): array
    {
        return $this->configuration['extras'] ?? [];
    }

    public function getDipsAttribute(): array
    {
        return $this->configuration['dips'] ?? [];
    }

    public function getDipExtraAttribute(): array
    {
        return $this->configuration['dip_extra'] ?? [];
    }

    public function getConfigurationTextAttribute(): string
    {
        // Solo aplicar lógica de hamburguesas a productos de categoría 1
        $isBurger = isset($this->product->category_id) && $this->product->category_id == 1;

        if (!$isBurger) {
            // Para postres, acompañamientos, etc.: mostrar configuración tal cual
            // desde configuration_data sin normalización
            $config = $this->configuration_data ?? [];
            $parts = [];

            // Mostrar todos los campos de configuración tal cual
            foreach ($config as $key => $value) {
                if (is_array($value)) {
                    if (!empty($value)) {
                        $parts[] = ucfirst($key) . ': ' . implode(', ', $value);
                    }
                } elseif (!empty($value)) {
                    $parts[] = ucfirst($key) . ': ' . $value;
                }
            }

            return implode(' | ', $parts);
        }

        // Lógica original para hamburguesas
        $parts = [];

        if ($this->medallones) {
            $parts[] = $this->medallones;
        }

        if ($this->tipo_medallon) {
            $parts[] = $this->tipo_medallon;
        }

        if (!empty($this->aderezos)) {
            $parts[] = 'Aderezos: ' . implode(', ', $this->aderezos);
        }

        if (!empty($this->extras)) {
            $parts[] = 'Extras: ' . implode(', ', $this->extras);
        }

        if (!empty($this->dips)) {
            $parts[] = 'Dips: ' . implode(', ', $this->dips);
        }

        if (!empty($this->dip_extra)) {
            $parts[] = 'Dip Extra: ' . implode(', ', $this->dip_extra);
        }

        return implode(' | ', $parts);
    }

    public function getProductNameWithConfigurationAttribute(): string
    {
        $name = $this->product->name;
        $configText = $this->configuration_text;

        if ($configText) {
            $name .= " ({$configText})";
        }

        return $name;
    }
}
