<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Playlist;
use App\Models\Song;

class PlaylistSongSeeder extends Seeder
{
    public function run(): void
    {
        $playlists = Playlist::all();
        $songs = Song::all();

        foreach ($playlists as $playlist) {
            $selectedSongs = $songs->random(rand(3, 5));

            $position = 1;
            foreach ($selectedSongs as $song) {
                // استفاده درست از pivot
                $playlist->songs()->syncWithoutDetaching([
                    $song->id => ['position' => $position++]
                ]);
            }
        }
    }
}