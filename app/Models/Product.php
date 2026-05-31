<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'base_price',
        'is_available',
        'is_featured',
        'sort_order',
        'allergens',
        'preparation_time',
        'default_sauce_configuration_id',
        'avg_rating',
        'review_count',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'allergens' => 'array',
            'avg_rating' => 'decimal:2',
        ];
    }

    // Relaciones
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultSauce(): BelongsTo
    {
        return $this->belongsTo(ProductConfiguration::class, 'default_sauce_configuration_id');
    }

    // Relaciones obsoletas removidas - ahora se usa ProductConfiguration global

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->approved();
    }

    public function allReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format((float) $this->base_price, 2);
    }

    public function getFormattedRatingAttribute(): string
    {
        return number_format($this->avg_rating, 1);
    }

    // Methods
    public function updateReviewStats(): void
    {
        $approvedReviews = $this->reviews();
        $this->avg_rating = $approvedReviews->avg('rating') ?? 0;
        $this->review_count = $approvedReviews->count();
        $this->save();
    }

    public function getAverageRating(): float
    {
        return $this->avg_rating ?? 0;
    }

    // Accessors obsoletos removidos - ahora se usa ProductConfiguration global
}
