<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Genre;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Pop',
            'Rock',
            'Hip Hop',
            'Jazz',
            'Blues',
            'Classical',
            'Electronic',
            'Country',
            'Reggae',
            'Metal',
            'R&B',
            'Soul',
            'Funk',
            'Disco',
            'Punk',
            'Gospel',
            'Folk',
            'Alternative',
            'Indie',
            'Dance',
            'Techno',
            'House',
            'Trance',
            'Dubstep',
            'Ska',
            'Latin',
            'Opera',
            'Soundtrack',
            'K-Pop',
            'J-Pop',
            'World Music',
            'Ambient',
            'Drum and Bass',
            'Trap',
            'Grime',
            'Lo-fi',
            'Chillout',
            'Electro',
            'Industrial',
            'Experimental'
        ];

        foreach ($genres as $genre) {
            Genre::updateOrCreate(['name' => $genre], []);
        }
    }
}