<?php

namespace App\Http\Controllers\api;


use App\Models\Playlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlaylistResource;
use App\Http\Requests\playlist\StorePlaylistRequest;
use App\Http\Requests\playlist\UpdatePlaylistRequest;

class PlaylistController extends Controller
{
    public function index()
    {
        return PlaylistResource::collection(
            auth()->user()
                ->playlists()
                ->withCount('songs')
                ->latest()
                ->paginate(10)
        );
    }

        public function store(StorePlaylistRequest $request)
        {
            $data = $request->validated(); 

            $playlist = Playlist::create([
                'user_id' => auth()->id(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_public' => $data['is_public'] ?? false,
                'cover_path' => $request->hasFile('cover')
                    ? $request->file('cover')->store('playlist-covers', 'public')
                    : null,
            ]);


            if (!empty($data['song_ids'])) {
                $syncData = [];

                foreach ($data['song_ids'] as $index => $songId) {
                    $syncData[$songId] = [
                        'position' => $index,
                    ];
                }

                $playlist->songs()->sync($syncData);
            }

            return new PlaylistResource(
                $playlist->load('songs')
            );
        }


    public function show($id)
    {
        $playlist = Playlist::with('songs')->findOrFail($id);

        if (!$playlist->is_public && $playlist->user_id !== auth()->id()) {
            abort(403);
        }

        return new PlaylistResource($playlist);
    }

    public function update(UpdatePlaylistRequest $request, $id)
    {
        $playlist = Playlist::findOrFail($id);

        if ($playlist->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validated();


        $playlist->update([
            'name' => $data['name'] ?? $playlist->name,
            'description' => $data['description'] ?? $playlist->description,
            'is_public' => $data['is_public'] ?? $playlist->is_public,
            'cover_path' => $request->hasFile('cover')
                ? $request->file('cover')->store('playlist-covers', 'public')
                : $playlist->cover_path,
        ]);


        if (!empty($data['song_ids'])) {
            $syncData = [];
            foreach ($data['song_ids'] as $index => $songId) {
                $syncData[$songId] = ['position' => $index];
            }
            $playlist->songs()->sync($syncData);
        }

        return new PlaylistResource(
            $playlist->load('songs')
        );
    }

    public function destroy($id)
    {
        $playlist = Playlist::findOrFail($id);

        if ($playlist->user_id !== auth()->id()) {
            abort(403);
        }

        $playlist->delete();

        return response()->json(['message' => 'Playlist deleted']);
    }
}