<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreSongTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_song(): void
    {
        // Arrange (آماده‌سازی)
        $user = User::factory()->create();
        $artist = Artist::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'title' => 'Test Song',
            'artist_id' => $artist->id,
            'duration' => 180,
            'path' => '/songs/test.mp3',
        ];

        // Act (ارسال درخواست)
        $response = $this->postJson('/api/v1/songs', $payload);

        // Assert (بررسی نتیجه)
        $response->assertStatus(201);

        $this->assertDatabaseHas('songs', [
            'title' => 'Test Song',
            'artist_id' => $artist->id,
        ]);
    }
}