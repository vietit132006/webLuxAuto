<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVote extends Model
{
    public const TYPE_HELPFUL = 'helpful';

    protected $fillable = [
        'review_id',
        'user_id',
        'type',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'review_id', 'review_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
