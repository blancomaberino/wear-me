<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingListItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'garment_id',
        'day_number',
        'occasion',
        'is_packed',
    ];

    protected function casts(): array
    {
        return [
            'is_packed' => 'boolean',
            'day_number' => 'integer',
        ];
    }

    public function packingList(): BelongsTo
    {
        return $this->belongsTo(PackingList::class);
    }

    public function garment(): BelongsTo
    {
        return $this->belongsTo(Garment::class);
    }
}
