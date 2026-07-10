<?php

namespace App\Services;

use App\Models\Playlist;
use App\Repositories\PlaylistRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class PlaylistService
{
    public function __construct(protected PlaylistRepository $repository) {}

    public function getAllByUser(int $userId): LengthAwarePaginator
    {
        return $this->repository->getByUserId($userId);
    }

    public function getById(int $id): ?Playlist
    {
        return $this->repository->findById($id);
    }

    protected function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', '"true"'], true);
    }

    public function create(array $data, ?UploadedFile $coverFile = null): Playlist
    {
        $playlist = $this->repository->create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $this->normalizeBoolean($data['is_public'] ?? false),
            'cover_path' => $coverFile
                ? $coverFile->store('playlist-covers', 'public')
                : null,
        ]);

        if (!empty($data['song_ids'])) {
            $this->repository->syncSongs($playlist, $data['song_ids']);
        }

        return $playlist->load('songs');
    }

    public function update(array $data, Playlist $playlist, ?UploadedFile $coverFile = null): Playlist
    {
        $updateData = [
            'name' => $data['name'] ?? $playlist->name,
            'description' => $data['description'] ?? $playlist->description,
            'is_public' => isset($data['is_public']) ? $this->normalizeBoolean($data['is_public']) : $playlist->is_public,
        ];

        if ($coverFile) {
            $updateData['cover_path'] = $coverFile->store('playlist-covers', 'public');
        }

        $playlist = $this->repository->update($playlist, $updateData);

        if (!empty($data['song_ids'])) {
            $this->repository->syncSongs($playlist, $data['song_ids']);
        }

        return $playlist->load('songs');
    }

    public function delete(Playlist $playlist): bool
    {
        return $this->repository->delete($playlist);
    }

    public function attachSong(Playlist $playlist, int $songId): void
    {
        $this->repository->attachSong($playlist, $songId);
    }

    public function detachSong(Playlist $playlist, int $songId): void
    {
        $this->repository->detachSong($playlist, $songId);
    }

    public function reorderSongs(Playlist $playlist, array $songIds): void
    {
        $this->repository->reorderSongs($playlist, $songIds);
    }
}
