<?php

namespace App\Models;

use App\Enums\ProcessingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TryOnResult extends Model
{
    use HasFactory;
    protected $table = 'tryon_results';

    protected $fillable = [
        'model_image_id',
        'source_tryon_result_id',
        'garment_id',
        'result_path',
        'provider_task_id',
        'provider',
        'status',
        'error_message',
        'is_favorite',
        'prompt_hint',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcessingStatus::class,
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modelImage(): BelongsTo
    {
        return $this->belongsTo(ModelImage::class);
    }

    public function sourceResult(): BelongsTo
    {
        return $this->belongsTo(TryOnResult::class, 'source_tryon_result_id');
    }

    public function garment(): BelongsTo
    {
        return $this->belongsTo(Garment::class);
    }

    public function garments(): BelongsToMany
    {
        return $this->belongsToMany(Garment::class, 'tryon_result_garment', 'tryon_result_id', 'garment_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function videos(): HasMany
    {
        return $this->hasMany(TryOnVideo::class, 'tryon_result_id');
    }

    public function getResultUrlAttribute(): ?string
    {
        return $this->result_path ? Storage::disk('public')->url($this->result_path) : null;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', ProcessingStatus::Completed);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
