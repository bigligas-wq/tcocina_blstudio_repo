<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReview extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'order_item_id',
        'rating',
        'comment',
        'status',
        'is_edited',
        'edited_at',
        'report_count',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that owns the review.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order that owns the review.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item that owns the review.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the images for the review.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductReviewImage::class);
    }

    /**
     * Get the history for the review.
     */
    public function history(): HasMany
    {
        return $this->hasMany(ProductReviewHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the reports for the review.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ProductReviewReport::class);
    }

    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include rejected reviews.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to filter by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the review can be edited by the user.
     */
    public function canEdit(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the review.
     */
    public function approve($notes = null): void
    {
        $this->status = 'approved';
        $this->save();

        // Record history
        $this->history()->create([
            'rating' => $this->rating,
            'comment' => $this->comment,
            'change_type' => 'status_changed',
            'changed_by' => 'admin',
            'changed_by_user_id' => auth()->id(),
            'change_notes' => $notes,
        ]);

        // Update product stats
        $this->product->updateReviewStats();
    }

    /**
     * Reject the review.
     */
    public function reject($notes = null): void
    {
        $this->status = 'rejected';
        $this->save();

        // Record history
        $this->history()->create([
            'rating' => $this->rating,
            'comment' => $this->comment,
            'change_type' => 'status_changed',
            'changed_by' => 'admin',
            'changed_by_user_id' => auth()->id(),
            'change_notes' => $notes,
        ]);
    }

    /**
     * Get the customer's first name for display.
     */
    public function getCustomerFirstNameAttribute(): string
    {
        return $this->user ? explode(' ', $this->user->name)[0] : 'Cliente';
    }
}
