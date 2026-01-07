<?php

namespace App\Http\Requests\song;

use Illuminate\Foundation\Http\FormRequest;

class GetDataSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // یا false اگر نیاز به auth داشته باشی
    }

    public function rules(): array
    {
        return [
            'song_file' => 'required|file|mimes:mp3,wav,ogg|max:51200', // 50MB
        ];
    }

}