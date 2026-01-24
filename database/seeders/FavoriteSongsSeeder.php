<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Song;

class FavoriteSongsSeeder extends Seeder
{
    public function run(): void
    {
        // گرفتن چند کاربر و آهنگ موجود
        $users = User::all();
        $songs = Song::all();

        if ($users->isEmpty() || $songs->isEmpty()) {
            $this->command->info('کاربر یا آهنگ کافی وجود ندارد!');
            return;
        }

        foreach ($users as $user) {
            // برای هر کاربر 3 تا آهنگ تصادفی به علاقه‌مندی اضافه کن
            $randomSongs = $songs->random(min(3, $songs->count()))->pluck('id')->toArray();
            $user->favoriteSongs()->syncWithoutDetaching($randomSongs);
        }

        $this->command->info('Favorite songs seeded successfully.');
    }
}