<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteSong\AddFavoriteRequest;
use App\Http\Requests\FavoriteSong\RemoveFavoriteRequest;
use App\Http\Resources\FavoriteSongResource;
use App\Http\Traits\ApiResponse;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;

class FavoriteSongController extends Controller
{
    use ApiResponse;

    public function __construct(protected FavoriteService $service) {}

    public function index(): JsonResponse
    {
        $favorites = $this->service->getAll(auth()->id());

        return $this->successResponse(
            FavoriteSongResource::collection($favorites),
            'Favorites retrieved successfully'
        );
    }

    public function add(AddFavoriteRequest $request): JsonResponse
    {
        $this->service->add(auth()->id(), $request->song_id);

        return $this->successResponse(null, 'Song added to favorites');
    }

    public function remove(RemoveFavoriteRequest $request): JsonResponse
    {
        $this->service->remove(auth()->id(), $request->song_id);

        return $this->successResponse(null, 'Song removed from favorites');
    }
}
