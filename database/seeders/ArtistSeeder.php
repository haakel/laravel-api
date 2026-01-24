<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Artist;

class ArtistSeeder extends Seeder
{
    public function run(): void
    {
        $artists = [
            ['name' => 'The Beatles'],
            ['name' => 'Michael Jackson'],
            ['name' => 'Taylor Swift'],
            ['name' => 'Ed Sheeran'],
            ['name' => 'Adele'],
        ];

        foreach ($artists as $artist) {
            Artist::updateOrCreate(['name' => $artist['name']], $artist);
        }
    }
}