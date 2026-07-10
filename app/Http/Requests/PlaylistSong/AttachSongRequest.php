<?php

namespace App\Http\Requests\PlaylistSong;

use Illuminate\Foundation\Http\FormRequest;

class AttachSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'song_id' => 'required|exists:songs,id',
        ];
    }
}
