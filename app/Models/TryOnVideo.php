<?php

namespace App\Models;

use App\Enums\ProcessingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TryOnVideo extends Model
{
    use HasFactory;
    protected $table = 'tryon_videos';

    protected $fillable = [
        'user_id',
        'tryon_result_id',
        'model_image_id',
        'garment_id',
        'video_path',
        'kling_task_id',
        'status',
        'duration_seconds',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProcessingStatus::class,
            'duration_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tryonResult(): BelongsTo
    {
        return $this->belongsTo(TryOnResult::class);
    }

    public function modelImage(): BelongsTo
    {
        return $this->belongsTo(ModelImage::class);
    }

    public function garment(): BelongsTo
    {
        return $this->belongsTo(Garment::class);
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path ? Storage::disk('public')->url($this->video_path) : null;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', ProcessingStatus::Completed);
    }
}
