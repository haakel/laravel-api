<?php

namespace App\Services;

use App\Models\Song;
use App\Repositories\SongRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SongService
{
    public function __construct(protected SongRepository $repository) {}

    public function getAll(?array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate(15, $filters);
    }

    public function getById(int $id): ?Song
    {
        return $this->repository->findById($id);
    }

    public function create(array $data, UploadedFile $songFile, ?UploadedFile $coverFile = null): Song
    {
        $user = auth()->user();
        $time = time();
        $titleSlug = Str::slug($data['title']);

        $songFilename = "{$titleSlug}_{$user->id}_{$time}.{$songFile->getClientOriginalExtension()}";
        $songPath = $songFile->storeAs('songs', $songFilename, 'public');

        $coverPath = null;
        if ($coverFile) {
            $coverFilename = "{$titleSlug}_{$user->id}_{$time}.{$coverFile->getClientOriginalExtension()}";
            $coverPath = $coverFile->storeAs('covers', $coverFilename, 'public');
        }

        $fullPath = storage_path('app/public/' . $songPath);
        $duration = $this->getAudioDuration($fullPath);

        return $this->repository->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'artist_id' => $data['artist_id'],
            'album' => $data['album'] ?? null,
            'year_id' => $data['year_id'] ?? null,
            'genre_id' => $data['genre_id'] ?? null,
            'duration' => $duration,
            'path' => $songPath,
            'cover_path' => $coverPath,
            'plays' => 0,
        ]);
    }

    public function update(array $data, Song $song, ?UploadedFile $coverFile = null): Song
    {
        $updateData = [
            'title' => $data['title'] ?? $song->title,
            'artist_id' => $data['artist_id'] ?? $song->artist_id,
            'album' => $data['album'] ?? $song->album,
            'year_id' => $data['year_id'] ?? $song->year_id,
            'genre_id' => $data['genre_id'] ?? $song->genre_id,
        ];

        if ($coverFile) {
            $time = time();
            $titleSlug = Str::slug($updateData['title']);
            $coverFilename = "{$titleSlug}_{$song->user_id}_{$time}.{$coverFile->getClientOriginalExtension()}";
            $updateData['cover_path'] = $coverFile->storeAs('covers', $coverFilename, 'public');
        }

        return $this->repository->update($song, $updateData);
    }

    public function delete(Song $song): bool
    {
        return $this->repository->delete($song);
    }

    public function extractMetadata(UploadedFile $file): array
    {
        $getID3 = new \getID3();
        $fullPath = $file->getRealPath();
        $info = $getID3->analyze($fullPath);
        \getid3_lib::CopyTagsToComments($info);

        return [
            'title' => $info['comments_html']['title'][0] ?? $info['tags']['id3v2']['title'][0] ?? '',
            'artist' => $info['comments_html']['artist'][0] ?? $info['tags']['id3v2']['artist'][0] ?? '',
            'album' => $info['comments_html']['album'][0] ?? $info['tags']['id3v2']['album'][0] ?? '',
            'year' => $info['comments_html']['year'][0] ?? $info['tags']['id3v2']['year'][0] ?? '',
            'genre' => $info['comments_html']['genre'][0] ?? $info['tags']['id3v2']['genre'][0] ?? '',
            'duration' => $info['playtime_seconds'] ?? 0,
            'bitrate' => $info['bitrate'] ?? 0,
        ];
    }

    protected function getAudioDuration(string $fullPath): int
    {
        $getID3 = new \getID3();
        $info = $getID3->analyze($fullPath);

        return isset($info['playtime_seconds'])
            ? (int) round($info['playtime_seconds'])
            : 0;
    }

    public function search(array $filters, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function incrementPlays(Song $song): void
    {
        $this->repository->incrementPlays($song);
    }
}
