<?php

namespace App\Http\Requests\song;

use Illuminate\Foundation\Http\FormRequest;

class DeleteSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // یا false اگر نیاز به auth داشته باشی
    }

    public function rules(): array
    {
        return [
            'song_id' => 'required|integer|exists:songs,id',
        ];
    }

}