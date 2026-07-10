<?php

namespace App\Http\Requests\FavoriteSong;

use Illuminate\Foundation\Http\FormRequest;

class AddFavoriteRequest extends FormRequest
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
