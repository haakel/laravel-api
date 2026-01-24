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
            'title' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'album' => 'nullable|string|max:255',
            'year' => 'nullable|exists:years',   
            'genre_id' => 'nullable|exists:genres,id',
            'song_file' => 'required|file|mimes:mp3,wav,ogg|max:51200', // 50MB
            'cover_file' => 'nullable|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ];
    }

}