<?php

namespace App\Models;

use App\Enums\GarmentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Garment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'path',
        'original_filename',
        'thumbnail_path',
        'category',
        'name',
        'description',
        'color_tags',
        'size_label',
        'brand',
        'material',
        'measurement_chest_cm',
        'measurement_length_cm',
        'measurement_waist_cm',
        'measurement_inseam_cm',
        'measurement_shoulder_cm',
        'measurement_sleeve_cm',
    ];

    protected function casts(): array
    {
        return [
            'category' => GarmentCategory::class,
            'color_tags' => 'array',
            'measurement_chest_cm' => 'decimal:1',
            'measurement_length_cm' => 'decimal:1',
            'measurement_waist_cm' => 'decimal:1',
            'measurement_inseam_cm' => 'decimal:1',
            'measurement_shoulder_cm' => 'decimal:1',
            'measurement_sleeve_cm' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tryonResults(): HasMany
    {
        return $this->hasMany(TryOnResult::class);
    }

    public function tryonResultsMulti(): BelongsToMany
    {
        return $this->belongsToMany(TryOnResult::class, 'tryon_result_garment', 'garment_id', 'tryon_result_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk('public')->url($this->thumbnail_path) : null;
    }

    public function getSizeDisplay(): string
    {
        if ($this->size_label) {
            return $this->brand ? "{$this->brand} {$this->size_label}" : $this->size_label;
        }
        return '';
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    protected static function booted(): void
    {
        static::deleting(function (Garment $garment) {
            Storage::disk('public')->delete($garment->path);
            if ($garment->thumbnail_path) {
                Storage::disk('public')->delete($garment->thumbnail_path);
            }
        });
    }
}
