<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutfitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'occasion' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'outfit_template_id' => 'nullable|integer|exists:outfit_templates,id',
            'garments' => 'required|array|min:1',
            'garments.*.garment_id' => [
                'required', 'integer',
                Rule::exists('garments', 'id')->where('user_id', $this->user()->id),
            ],
            'garments.*.slot_label' => 'nullable|string|max:100',
        ];
    }
}
