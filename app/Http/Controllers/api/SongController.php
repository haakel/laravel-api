<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\song\StoreSongRequest;
use App\Http\Requests\song\EditSongRequest;
use App\Http\Requests\song\GetDataSongRequest;
use App\Http\Resources\SongResource;
use App\Http\Traits\ApiResponse;
use App\Models\Song;
use App\Services\SongService;
use App\Services\MusicBrainzService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SongController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SongService $songService,
        protected MusicBrainzService $musicBrainzService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'title' => $request->title,
            'artist_id' => $request->artist_id,
            'album' => $request->album,
            'genre_id' => $request->genre_id,
            'year_id' => $request->year_id,
        ]);

        $songs = $this->songService->getAll($filters);

        return $this->paginatedResponse(SongResource::collection($songs));
    }

    public function store(StoreSongRequest $request): JsonResponse
    {
        $song = $this->songService->create(
            $request->validated(),
            $request->file('song_file'),
            $request->file('cover_file')
        );

        return $this->successResponse(
            new SongResource($song->load(['artist', 'genre', 'year'])),
            'Song created successfully',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $this->authorize('view', $song);

        return $this->successResponse(new SongResource($song));
    }

    public function update(EditSongRequest $request, int $id): JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $this->authorize('update', $song);

        $updated = $this->songService->update(
            $request->validated(),
            $song,
            $request->file('cover_file')
        );

        return $this->successResponse(
            new SongResource($updated->load(['artist', 'genre', 'year'])),
            'Song updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $this->authorize('delete', $song);

        $this->songService->delete($song);

        return $this->successResponse(null, 'Song deleted successfully');
    }

    public function getMetadata(GetDataSongRequest $request): JsonResponse
    {
        $metadata = $this->songService->extractMetadata($request->file('song_file'));

        $musicBrainzData = [];
        if (!empty($metadata['title']) || !empty($metadata['artist'])) {
            $results = $this->musicBrainzService->searchByTitleAndArtist(
                $metadata['title'],
                $metadata['artist']
            );
            $musicBrainzData = $results[0] ?? [];
        }

        return $this->successResponse([
            'metadata' => $metadata,
            'music_brainz' => $musicBrainzData,
        ]);
    }

    public function searchMusicBrainz(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'artist' => 'nullable|string',
        ]);

        $results = $this->musicBrainzService->searchByTitleAndArtist(
            $request->name,
            $request->artist
        );

        if (empty($results)) {
            return $this->errorResponse('No recordings found', 404);
        }

        return $this->successResponse([
            'count' => count($results),
            'results' => $results,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string',
            'artist_id' => 'nullable|exists:artists,id',
            'album' => 'nullable|string',
            'genre_id' => 'nullable|exists:genres,id',
            'year_id' => 'nullable|exists:years,id',
        ]);

        $filters = array_filter([
            'title' => $request->title,
            'artist_id' => $request->artist_id,
            'album' => $request->album,
            'genre_id' => $request->genre_id,
            'year_id' => $request->year_id,
        ]);

        $songs = $this->songService->search($filters);

        return $this->paginatedResponse(SongResource::collection($songs));
    }

    public function stream(int $id): StreamedResponse|JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $filePath = storage_path('app/public/' . $song->path);

        if (!file_exists($filePath)) {
            return $this->errorResponse('Audio file not found', 404);
        }

        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'audio/mpeg';

        // Increment play count
        $this->songService->incrementPlays($song);

        return response()->stream(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline; filename="' . basename($song->path) . '"',
        ]);
    }
}
