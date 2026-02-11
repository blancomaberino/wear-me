<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OutfitSuggestion extends Model
{
    use HasFactory;
    protected $fillable = [
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

    public function garments(): BelongsToMany
    {
        return $this->belongsToMany(Garment::class, 'outfit_suggestion_garment')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Get garments from the pivot table, falling back to the legacy garment_ids JSON column.
     */
    public function resolveGarments()
    {
        $pivotGarments = $this->garments;
        if ($pivotGarments->isNotEmpty()) {
            return $pivotGarments;
        }

        // Legacy fallback: load from JSON column
        return Garment::whereIn('id', $this->garment_ids ?? [])
            ->where('user_id', $this->user_id)
            ->get();
    }
}
