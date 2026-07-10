<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PlaylistApiTest extends TestCase
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

    public function test_user_can_list_playlists(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/playlists');

        $response->assertOk();
    }

    public function test_user_can_create_playlist(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/playlists', [
            'name' => 'My Playlist',
            'description' => 'A great playlist',
            'is_public' => true,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('playlists', [
            'name' => 'My Playlist',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_update_own_playlist(): void
    {
        $playlist = $this->user->playlists()->create([
            'name' => 'Old Name',
            'is_public' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->putJson("/api/v1/playlists/{$playlist->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('playlists', ['id' => $playlist->id, 'name' => 'New Name']);
    }

    public function test_user_can_delete_own_playlist(): void
    {
        $playlist = $this->user->playlists()->create([
            'name' => 'Delete Me',
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->deleteJson("/api/v1/playlists/{$playlist->id}");

        $response->assertOk();
        $this->assertSoftDeleted('playlists', ['id' => $playlist->id]);
    }

    public function test_user_can_add_song_to_playlist(): void
    {
        $playlist = $this->user->playlists()->create(['name' => 'Test']);
        $artist = Artist::factory()->create();
        $song = Song::factory()->create([
            'user_id' => $this->user->id,
            'artist_id' => $artist->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson("/api/v1/playlists/{$playlist->id}/songs", [
            'song_id' => $song->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('playlist_song', [
            'playlist_id' => $playlist->id,
            'song_id' => $song->id,
        ]);
    }
}
