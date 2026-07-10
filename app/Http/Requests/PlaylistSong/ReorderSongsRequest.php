<?php

namespace App\Http\Requests\PlaylistSong;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSongsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'song_ids' => 'required|array',
            'song_ids.*' => 'exists:songs,id',
        ];
    }
}
