<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Export extends Model
{
    protected $fillable = [
        'file_path',
        'status',
        'file_size_bytes',
        'include_images',
        'include_results',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'include_images' => 'boolean',
            'include_results' => 'boolean',
            'expires_at' => 'datetime',
            'file_size_bytes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->status !== 'completed' || !$this->file_path) {
            return null;
        }
        return route('export.download', $this);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
