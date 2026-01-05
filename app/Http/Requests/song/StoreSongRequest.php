<?php

namespace App\Http\Requests\song;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // یا false اگر نیاز به auth داشته باشی
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'album' => 'nullable|string|max:255',
            'year_id' => 'nullable|exists:years,id',
            'genre_id' => 'nullable|exists:genres,id',
            'duration' => 'nullable|integer|min:0',
            'path' => 'required|string|max:255',
            'cover_path' => 'nullable|string|max:255',
            'plays' => 'nullable|integer',
        ];
    }

}