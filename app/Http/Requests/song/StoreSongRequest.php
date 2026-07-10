<?php

namespace App\Http\Requests\Song;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'album' => 'nullable|string|max:255',
            'year_id' => 'nullable|exists:years,id',
            'genre_id' => 'nullable|exists:genres,id',
            'song_file' => 'required|file|mimes:mp3,wav,ogg,m4a,aac,mp4|max:51200',
            'cover_file' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }
}
