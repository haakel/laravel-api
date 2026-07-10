<?php

namespace App\Repositories;

use App\Models\Song;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SongRepository
{
    public function __construct(protected Song $model) {}

    public function paginate(int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['artist', 'genre', 'year']);

        if (!empty($filters['title'])) {
            $query->where('title', 'like', "%{$filters['title']}%");
        }

        if (!empty($filters['artist_id'])) {
            $query->where('artist_id', $filters['artist_id']);
        }

        if (!empty($filters['album'])) {
            $query->where('album', 'like', "%{$filters['album']}%");
        }

        if (!empty($filters['genre_id'])) {
            $query->where('genre_id', $filters['genre_id']);
        }

        if (!empty($filters['year_id'])) {
            $query->where('year_id', $filters['year_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Song
    {
        return $this->model->with(['artist', 'genre', 'year', 'user'])->find($id);
    }

    public function create(array $data): Song
    {
        return $this->model->create($data);
    }

    public function update(Song $song, array $data): Song
    {
        $song->update($data);
        return $song->fresh();
    }

    public function delete(Song $song): bool
    {
        return $song->delete();
    }

    public function incrementPlays(Song $song): void
    {
        $song->increment('plays');
    }

    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }
}
