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
        return $this->items()->where('is_packed', true)->count();
    }

    public function getTotalCountAttribute(): int
    {
        return $this->items()->count();
    }
}
