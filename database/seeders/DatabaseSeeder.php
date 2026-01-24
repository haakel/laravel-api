<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
        {
            // کاربران
            $this->call(UserSeeder::class);

            // هنرمندان
            $this->call(ArtistSeeder::class);

            // ژانرها
            $this->call(GenreSeeder::class);

            // سال‌ها
            $this->call(YearSeeder::class);

            // آهنگ‌ها
            $this->call(SongSeeder::class);

            // پلی‌لیست‌ها
            $this->call(PlaylistSeeder::class);
        }
}