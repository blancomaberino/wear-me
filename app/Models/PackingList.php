<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'destination',
        'start_date',
        'end_date',
        'occasions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'occasions' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackingListItem::class);
    }

    public function getPackedCountAttribute(): int
    {
        // Use eager-loaded count if available, fallback to query
        if (array_key_exists('packed_count', $this->attributes)) {
            return (int) $this->attributes['packed_count'];
        }
        return $this->items()->where('is_packed', true)->count();
    }

    public function getTotalCountAttribute(): int
    {
        // Use eager-loaded count if available, fallback to query
        if (array_key_exists('total_count', $this->attributes)) {
            return (int) $this->attributes['total_count'];
        }
        return $this->items()->count();
    }
}
