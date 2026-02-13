<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutfitTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'occasion',
        'description',
        'slots',
        'icon',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'slots' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outfits(): HasMany
    {
        return $this->hasMany(Outfit::class);
    }
}
