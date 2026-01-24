<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Playlist;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $playlists = [
            [
                'user_id' => 1,
                'name' => 'Chill Vibes',
                'description' => 'Relaxing and soft tracks',
                'is_public' => true,
                'cover_path' => 'covers/chill_vibes.jpg',
            ],
            [
                'user_id' => 1,
                'name' => 'Workout Hits',
                'description' => 'High energy songs for gym',
                'is_public' => true,
                'cover_path' => 'covers/workout_hits.jpg',
            ],
            [
                'user_id' => 2,
                'name' => 'Jazz Classics',
                'description' => 'Best of Jazz music',
                'is_public' => false,
                'cover_path' => 'covers/jazz_classics.jpg',
            ],
            [
                'user_id' => 2,
                'name' => 'Pop Favorites',
                'description' => 'Popular pop songs',
                'is_public' => true,
                'cover_path' => 'covers/pop_favorites.jpg',
            ],
            [
                'user_id' => 3,
                'name' => 'Indie Collection',
                'description' => 'Indie songs from various artists',
                'is_public' => true,
                'cover_path' => 'covers/indie_collection.jpg',
            ],
        ];

        foreach ($playlists as $playlist) {
            Playlist::updateOrCreate(['name' => $playlist['name']], $playlist);
        }
    }
}