<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaletteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'colors' => 'required|array|max:50',
            'colors.*' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }
}
