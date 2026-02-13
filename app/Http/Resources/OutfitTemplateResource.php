<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'occasion' => $this->occasion,
            'description' => $this->description,
            'slots' => $this->slots,
            'icon' => $this->icon,
            'is_system' => $this->is_system,
        ];
    }
}
