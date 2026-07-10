<?php

namespace Database\Factories;

use App\Models\Song;
use App\Models\User;
use App\Models\Artist;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFactory extends Factory
{
    protected $model = Song::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(2),
            'artist_id' => Artist::factory(),
            'album' => fake()->optional()->word(),
            'year_id' => null,
            'genre_id' => Genre::factory(),
            'duration' => rand(120, 300),
            'path' => 'songs/' . fake()->uuid() . '.mp3',
            'cover_path' => null,
            'plays' => 0,
        ];
    }
}
