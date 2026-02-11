<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'visitor_hash',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function shareLink(): BelongsTo
    {
        return $this->belongsTo(ShareLink::class);
    }
}
