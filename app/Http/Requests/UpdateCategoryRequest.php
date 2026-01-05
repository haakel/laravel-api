<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // اگر id از URL میاد، باید از route parameter بخونیمش
        $categoryId = $this->route('id');

        return [
            'name'        => "sometimes|required|string|max:255|unique:categories,name,{$categoryId}",
            'description' => 'nullable|string',
        ];
    }
}