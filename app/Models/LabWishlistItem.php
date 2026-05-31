<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabWishlistItem extends Model
{
    protected $fillable = ['user_id', 'lab_improvement_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function improvement(): BelongsTo
    {
        return $this->belongsTo(LabImprovement::class, 'lab_improvement_id');
    }
}
