<?php

namespace App\Services;

use App\Models\Playlist;
use App\Repositories\PlaylistRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class PlaylistService
{
    /**
     * سرويس مديريت پلي ليست ها - ساخت، ويراش، حذف و مرتب‌سازي آهنگ‌ها
     *
     * @param PlaylistRepository $repository
     */
    public function __construct(protected PlaylistRepository $repository) {}

    /**
     * دریافت پلی‌لیست‌های کاربر
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByUser(int $userId): LengthAwarePaginator
    {
        return $this->repository->getByUserId($userId);
    }

    /**
     * دریافت پلی‌لیست با شناسه + آهنگ‌های آن
     *
     * @param int $id
     * @return Playlist|null
     */
    public function getById(int $id): ?Playlist
    {
        return $this->repository->findById($id);
    }

    protected function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', '"true"'], true);
    }

    /**
     * ساخت پلی‌لیست جدید
     *
     * @param array $data  اطلاعات (name, description, is_public, song_ids)
     * @return Playlist
     */
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

    /**
     * ویرایش پلی‌لیست
     *
     * @param Playlist $playlist
     * @param array $data
     * @return Playlist
     */
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

    /**
     * حذف پلی‌لیست
     *
     * @param Playlist $playlist
     * @return bool
     */
    public function delete(Playlist $playlist): bool
    {
        return $this->repository->delete($playlist);
    }

    /**
     * افزودن آهنگ به پلی‌لیست
     *
     * @param Playlist $playlist
     * @param int $songId
     * @return bool
     */
    public function attachSong(Playlist $playlist, int $songId): void
    {
        $this->repository->attachSong($playlist, $songId);
    }

    /**
     * حذف آهنگ از پلی‌لیست
     *
     * @param Playlist $playlist
     * @param int $songId
     * @return bool
     */
    public function detachSong(Playlist $playlist, int $songId): void
    {
        $this->repository->detachSong($playlist, $songId);
    }

    /**
     * مرتب‌سازی مجدد آهنگ‌ها در پلی‌لیست
     *
     * @param Playlist $playlist
     * @param array $songIds
     * @return bool
     */
    public function reorderSongs(Playlist $playlist, array $songIds): void
    {
        $this->repository->reorderSongs($playlist, $songIds);
    }
}
