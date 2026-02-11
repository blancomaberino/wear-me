<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutfitSuggestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'garment_ids',
        'suggestion_text',
        'occasion',
        'is_saved',
    ];

    protected function casts(): array
    {
        return [
            'garment_ids' => 'array',
            'is_saved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function garments()
    {
        return Garment::whereIn('id', $this->garment_ids ?? [])->get();
    }
}
