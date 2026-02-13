<?php

namespace App\Http\Requests;

use App\Enums\GarmentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreGarmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp,avif|max:10240',
            'category' => ['required', Rule::enum(GarmentCategory::class)],
        ];
    }
}
