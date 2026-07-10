<?php

namespace App\Repositories;

use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FavoriteRepository
{
    public function __construct(protected Song $model) {}

    public function getByUserId(int $userId): Collection
    {
        return $this->model
            ->whereHas('favoritedBy', fn($q) => $q->where('user_id', $userId))
            ->with(['artist', 'genre'])
            ->get();
    }

    public function add(int $userId, int $songId): void
    {
        $user = User::find($userId);
        $user?->favoriteSongs()->syncWithoutDetaching($songId);
    }

    public function remove(int $userId, int $songId): void
    {
        $user = User::find($userId);
        $user?->favoriteSongs()->detach($songId);
    }

    public function isFavorited(int $userId, int $songId): bool
    {
        return $this->model
            ->where('id', $songId)
            ->whereHas('favoritedBy', fn($q) => $q->where('user_id', $userId))
            ->exists();
    }
}
