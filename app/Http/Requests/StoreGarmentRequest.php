<?php

namespace App\Http\Requests;

use App\Enums\GarmentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGarmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,avif|max:10240',
            'category' => ['required', Rule::enum(GarmentCategory::class)],
            'clothing_type' => 'nullable|string|max:50',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
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
