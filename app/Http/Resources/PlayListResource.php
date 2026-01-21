<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_public' => $this->is_public,
            'cover_path' => $this->cover_path ? asset("storage/playlist/{$this->cover_path}") : null,
            'songs_count' => $this->whenCounted('songs'),
            'songs' => SongResource::collection($this->whenLoaded('songs')),
            'created_at' => $this->created_at,
        ];
    }
}