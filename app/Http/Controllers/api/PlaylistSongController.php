<?php

namespace App\Http\Controllers\api;

use App\Models\Playlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlaylistSongController extends Controller
{
    public function attach(Request $request, $playlistId)
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $playlist = Playlist::findOrFail($playlistId);

        if ($playlist->user_id !== auth()->id()) {
            abort(403);
        }

        $lastPosition = $playlist->songs()->max('position') ?? 0;

        $playlist->songs()->syncWithoutDetaching([
            $request->song_id => ['position' => $lastPosition + 1]
        ]);

        return response()->json(['message' => 'Song added to playlist']);
    }

    public function detach($playlistId, $songId)
    {
        $playlist = Playlist::findOrFail($playlistId);

        if ($playlist->user_id !== auth()->id()) {
            abort(403);
        }

        $playlist->songs()->detach($songId);

        return response()->json(['message' => 'Song removed from playlist']);
    }

    /**
     * reorder songs (drag & drop)
     * کتابخونه drag & drop
     * function moveItem(array, from, to) {
     *  const updated = [...array];
     *  const [item] = updated.splice(from, 1);
     *  updated.splice(to, 0, item);
     *  return updated;
     *  }
     * payload: [ { id: 9 }, { id: 5 }, { id: 2 }, { id: 1 },]
     */
    public function reorder(Request $request, $playlistId)
    {
        $playlist = Playlist::findOrFail($playlistId);

        if ($playlist->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'song_ids' => 'required|array',
            'song_ids.*' => 'exists:songs,id',
        ]);

        foreach ($request->song_ids as $index => $songId) {
            $playlist->songs()->updateExistingPivot(
                $songId,
                ['position' => $index + 1]
            );
        }

        return response()->json(['message' => 'Playlist reordered']);
    }

}