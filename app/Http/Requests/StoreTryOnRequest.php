<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTryOnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('garment_id') && !$this->has('garment_ids')) {
            $this->merge(['garment_ids' => [$this->garment_id]]);
        }
    }

    public function rules(): array
    {
        return [
            'model_image_id' => 'required_without:source_tryon_result_id|nullable|integer|exists:model_images,id',
            'source_tryon_result_id' => 'nullable|integer|exists:tryon_results,id',
            'garment_ids' => 'required|array|min:1|max:5',
            'garment_ids.*' => 'required|integer|exists:garments,id',
            'prompt_hint' => 'nullable|string|max:200',
        ];
    }
}
