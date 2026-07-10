<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Song;
use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_user_can_list_favorites(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/favorites');

        $response->assertOk();
    }

    public function test_user_can_add_favorite(): void
    {
        $artist = Artist::factory()->create();
        $song = Song::factory()->create([
            'user_id' => $this->user->id,
            'artist_id' => $artist->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/favorites', [
            'song_id' => $song->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('song_user', [
            'user_id' => $this->user->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_user_can_remove_favorite(): void
    {
        $artist = Artist::factory()->create();
        $song = Song::factory()->create([
            'user_id' => $this->user->id,
            'artist_id' => $artist->id,
        ]);
        $this->user->favoriteSongs()->attach($song->id);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->deleteJson('/api/v1/favorites', [
            'song_id' => $song->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('song_user', [
            'user_id' => $this->user->id,
            'song_id' => $song->id,
        ]);
    }
}
