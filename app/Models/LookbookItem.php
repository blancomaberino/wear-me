<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LookbookItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'itemable_type',
        'itemable_id',
        'note',
        'sort_order',
    ];

    public function lookbook(): BelongsTo
    {
        return $this->belongsTo(Lookbook::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
