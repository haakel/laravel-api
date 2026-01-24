<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowSongResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'artist_id' => $this->artist_id,
            'album' => $this->album,
            'year' => $this->year,
            'genre_id' => $this->genre_id,
            'duration' => $this->duration,
            'path' => $this->path ? asset("storage/{$this->path}") : null,
            'cover_path' => $this->cover_path ? asset("storage/{$this->cover_path}") : null,
            'plays' => $this->plays
        ];
    }
}