<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * تبدیل داده آهنگ مورد علاقه به فرمت استاندارد API
 */
class FavoriteSongResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => new ArtistResource($this->whenLoaded('artist')),
            'album' => $this->album,
            'duration' => $this->duration,
            'cover_url' => $this->cover_path
                ? asset("storage/{$this->cover_path}")
                : asset('images/default-cover.jpg'),
        ];
    }
}
