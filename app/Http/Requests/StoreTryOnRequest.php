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
            'model_image_id' => 'required_without:source_tryon_result_id|nullable|integer',
            'source_tryon_result_id' => 'nullable|integer',
            'garment_ids' => 'required|array|min:1|max:5',
            'garment_ids.*' => 'required|integer',
            'prompt_hint' => 'nullable|string|max:200',
        ];
    }
}
