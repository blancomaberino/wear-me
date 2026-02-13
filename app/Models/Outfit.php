<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Outfit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'occasion',
        'notes',
        'harmony_score',
        'outfit_template_id',
    ];

    protected function casts(): array
    {
        return [
            'harmony_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OutfitTemplate::class, 'outfit_template_id');
    }

    public function garments(): BelongsToMany
    {
        return $this->belongsToMany(Garment::class, 'outfit_garments')
            ->withPivot('slot_label', 'sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }
}
