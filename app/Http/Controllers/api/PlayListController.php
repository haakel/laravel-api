<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\playlist\StorePlaylistRequest;
use App\Http\Requests\playlist\UpdatePlaylistRequest;
use App\Http\Resources\PlaylistResource;
use App\Http\Traits\ApiResponse;
use App\Models\Playlist;
use App\Services\PlaylistService;
use Illuminate\Http\JsonResponse;

class PlaylistController extends Controller
{
    use ApiResponse;

    public function __construct(protected PlaylistService $service) {}

    public function index(): JsonResponse
    {
        $playlists = $this->service->getAllByUser(auth()->id());

        return $this->paginatedResponse(PlaylistResource::collection($playlists));
    }

    public function store(StorePlaylistRequest $request): JsonResponse
    {
        $playlist = $this->service->create(
            $request->validated(),
            $request->file('cover')
        );

        return $this->successResponse(
            new PlaylistResource($playlist),
            'Playlist created successfully',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $playlist = $this->service->getById($id);

        if (!$playlist) {
            return $this->errorResponse('Playlist not found', 404);
        }

        $this->authorize('view', $playlist);

        return $this->successResponse(new PlaylistResource($playlist));
    }

    public function update(UpdatePlaylistRequest $request, int $id): JsonResponse
    {
        $playlist = $this->service->getById($id);

        if (!$playlist) {
            return $this->errorResponse('Playlist not found', 404);
        }

        $this->authorize('update', $playlist);

        $updated = $this->service->update(
            $request->validated(),
            $playlist,
            $request->file('cover')
        );

        return $this->successResponse(
            new PlaylistResource($updated),
            'Playlist updated successfully'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $playlist = $this->service->getById($id);

        if (!$playlist) {
            return $this->errorResponse('Playlist not found', 404);
        }

        $this->authorize('delete', $playlist);

        $this->service->delete($playlist);

        return $this->successResponse(null, 'Playlist deleted successfully');
    }
}
