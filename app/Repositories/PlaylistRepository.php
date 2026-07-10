<?php

namespace App\Repositories;

use App\Models\Playlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PlaylistRepository
{
    public function __construct(protected Playlist $model) {}

    public function getByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->withCount('songs')
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Playlist
    {
        return $this->model->with('songs')->find($id);
    }

    public function create(array $data): Playlist
    {
        return $this->model->create($data);
    }

    public function update(Playlist $playlist, array $data): Playlist
    {
        $playlist->update($data);
        return $playlist->fresh();
    }

    public function delete(Playlist $playlist): bool
    {
        return $playlist->delete();
    }

    public function syncSongs(Playlist $playlist, array $songIds): void
    {
        $syncData = [];
        foreach ($songIds as $index => $songId) {
            $syncData[$songId] = ['position' => $index];
        }
        $playlist->songs()->sync($syncData);
    }

    public function attachSong(Playlist $playlist, int $songId): void
    {
        $lastPosition = $playlist->songs()->max('position') ?? 0;
        $playlist->songs()->syncWithoutDetaching([
            $songId => ['position' => $lastPosition + 1],
        ]);
    }

    public function detachSong(Playlist $playlist, int $songId): void
    {
        $playlist->songs()->detach($songId);
    }

    public function reorderSongs(Playlist $playlist, array $songIds): void
    {
        foreach ($songIds as $index => $songId) {
            $playlist->songs()->updateExistingPivot($songId, ['position' => $index + 1]);
        }
    }
}
