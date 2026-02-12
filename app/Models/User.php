<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const MAX_GARMENTS = 200;

    protected $fillable = [
        'name',
        'email',
        'locale',
        'password',
        'color_palette',
        'measurement_unit',
        'height_cm',
        'weight_kg',
        'chest_cm',
        'waist_cm',
        'hips_cm',
        'inseam_cm',
        'shoe_size_eu',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'color_palette' => 'array',
            'height_cm' => 'decimal:1',
            'weight_kg' => 'decimal:1',
            'chest_cm' => 'decimal:1',
            'waist_cm' => 'decimal:1',
            'hips_cm' => 'decimal:1',
            'inseam_cm' => 'decimal:1',
            'shoe_size_eu' => 'decimal:1',
        ];
    }

    public function modelImages(): HasMany
    {
        return $this->hasMany(ModelImage::class);
    }

    public function garments(): HasMany
    {
        return $this->hasMany(Garment::class);
    }

    public function tryonResults(): HasMany
    {
        return $this->hasMany(TryOnResult::class);
    }

    public function tryonVideos(): HasMany
    {
        return $this->hasMany(TryOnVideo::class);
    }

    public function outfitSuggestions(): HasMany
    {
        return $this->hasMany(OutfitSuggestion::class);
    }

    public function lookbooks(): HasMany
    {
        return $this->hasMany(Lookbook::class);
    }

    public function shareLinks(): HasMany
    {
        return $this->hasMany(ShareLink::class);
    }

    public function outfits(): HasMany
    {
        return $this->hasMany(Outfit::class);
    }

    public function packingLists(): HasMany
    {
        return $this->hasMany(PackingList::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(Export::class);
    }

    public function hasMeasurements(): bool
    {
        return $this->height_cm !== null
            || $this->chest_cm !== null
            || $this->waist_cm !== null
            || $this->hips_cm !== null;
    }

    public function getFormattedMeasurements(string $unit = 'metric'): array
    {
        $measurements = [
            'height' => $this->height_cm,
            'weight' => $this->weight_kg,
            'chest' => $this->chest_cm,
            'waist' => $this->waist_cm,
            'hips' => $this->hips_cm,
            'inseam' => $this->inseam_cm,
            'shoe_size' => $this->shoe_size_eu,
        ];

        if ($unit === 'imperial') {
            $cmToIn = fn ($v) => $v !== null ? round($v / 2.54, 1) : null;
            $measurements['height'] = $cmToIn($this->height_cm);
            $measurements['weight'] = $this->weight_kg !== null ? round($this->weight_kg * 2.205, 1) : null;
            $measurements['chest'] = $cmToIn($this->chest_cm);
            $measurements['waist'] = $cmToIn($this->waist_cm);
            $measurements['hips'] = $cmToIn($this->hips_cm);
            $measurements['inseam'] = $cmToIn($this->inseam_cm);
        }

        return $measurements;
    }
}
