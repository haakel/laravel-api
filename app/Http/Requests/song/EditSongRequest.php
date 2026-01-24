<?php

namespace App\Http\Requests\song;

use Illuminate\Foundation\Http\FormRequest;

class EditSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // یا false اگر نیاز به auth داشته باشی
    }

    public function rules(): array
    {
        return [
            'song_id' => 'required|exists:songs,id',
            'title' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'album' => 'nullable|string|max:255',
            'year_id' => 'nullable|exists:years,id',
            'genre_id' => 'nullable|exists:genres,id',
            'cover_file' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ];
    }

}