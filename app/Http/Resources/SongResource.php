<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'artist' => new ArtistResource($this->whenLoaded('artist')),
            'genre' => new GenreResource($this->whenLoaded('genre')),
            'year' => $this->whenLoaded('year', fn() => $this->year?->value),
            'album' => $this->album,
            'duration' => $this->duration,
            'cover_url' => $this->cover_path
                ? asset("storage/{$this->cover_path}")
                : asset('images/default-cover.jpg'),
            'plays' => $this->plays,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}