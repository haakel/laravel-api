<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Song;
use App\Models\Year;

class SongSeeder extends Seeder
{
    public function run(): void
    {
        $yearIds = Year::pluck('id');

        $songs = [
            [
                'user_id' => 1,
                'title' => 'Song One',
                'artist_id' => 1,
                'album' => 'Album One',
                'year_id' => $yearIds[0] ?? null,
                'genre_id' => 1,
                'duration' => 210,
                'path' => 'songs/song_one.mp3',
                'cover_path' => 'covers/song_one.jpg',
                'plays' => 0,
            ],
            [
                'user_id' => 1,
                'title' => 'Song Two',
                'artist_id' => 2,
                'album' => 'Album Two',
                'year_id' => $yearIds[1] ?? null,
                'genre_id' => 2,
                'duration' => 180,
                'path' => 'songs/song_two.mp3',
                'cover_path' => 'covers/song_two.jpg',
                'plays' => 0,
            ],
            [
                'user_id' => 2,
                'title' => 'Song Three',
                'artist_id' => 3,
                'album' => 'Album Three',
                'year_id' => $yearIds[2] ?? null,
                'genre_id' => 3,
                'duration' => 200,
                'path' => 'songs/song_three.mp3',
                'cover_path' => 'covers/song_three.jpg',
                'plays' => 0,
            ],
            [
                'user_id' => 2,
                'title' => 'Song Four',
                'artist_id' => 1,
                'album' => 'Album Four',
                'year_id' => $yearIds[3] ?? null,
                'genre_id' => 4,
                'duration' => 240,
                'path' => 'songs/song_four.mp3',
                'cover_path' => 'covers/song_four.jpg',
                'plays' => 0,
            ],
            [
                'user_id' => 3,
                'title' => 'Song Five',
                'artist_id' => 2,
                'album' => 'Album Five',
                'year_id' => $yearIds[4] ?? null,
                'genre_id' => 5,
                'duration' => 230,
                'path' => 'songs/song_five.mp3',
                'cover_path' => 'covers/song_five.jpg',
                'plays' => 0,
            ],
        ];

        foreach ($songs as $song) {
            Song::updateOrCreate(['title' => $song['title']], $song);
        }
    }
}