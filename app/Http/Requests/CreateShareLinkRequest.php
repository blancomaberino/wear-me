<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShareLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shareable_type' => 'required|string|in:lookbook,tryon_result',
            'shareable_id' => 'required|integer',
            'expires_in' => 'nullable|string|in:1_day,7_days,30_days,never',
        ];
    }
}
