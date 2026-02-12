<?php

namespace App\Http\Requests;

use App\Enums\GarmentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGarmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('garment'));
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => ['nullable', Rule::enum(GarmentCategory::class)],
            'clothing_type' => 'nullable|string|max:50',
            'size_label' => 'nullable|string|max:20',
            'brand' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'measurement_chest_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_length_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_waist_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_inseam_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_shoulder_cm' => 'nullable|numeric|min:0|max:300',
            'measurement_sleeve_cm' => 'nullable|numeric|min:0|max:300',
        ];
    }
}
