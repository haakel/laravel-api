<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;

class SongApiTest extends TestCase
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

    public function test_user_can_list_songs(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/songs');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_list_songs(): void
    {
        $response = $this->getJson('/api/v1/songs');

        $response->assertUnauthorized();
    }

    public function test_user_can_create_song(): void
    {
        $artist = Artist::factory()->create();
        $genre = Genre::factory()->create();

        $songFile = UploadedFile::fake()->createWithContent('test-song.mp3', str_repeat('x', 1024));

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/songs', [
            'title' => 'Test Song',
            'artist_id' => $artist->id,
            'genre_id' => $genre->id,
            'song_file' => $songFile,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('songs', [
            'title' => 'Test Song',
            'artist_id' => $artist->id,
        ]);
    }

    public function test_user_can_show_song(): void
    {
        $artist = Artist::factory()->create();
        $song = $this->user->songs()->create([
            'title' => 'My Song',
            'artist_id' => $artist->id,
            'path' => 'songs/test.mp3',
            'duration' => 180,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/songs/{$song->id}");

        $response->assertOk();
    }

    public function test_user_can_update_own_song(): void
    {
        $artist = Artist::factory()->create();
        $song = $this->user->songs()->create([
            'title' => 'Old Title',
            'artist_id' => $artist->id,
            'path' => 'songs/test.mp3',
            'duration' => 180,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->putJson("/api/v1/songs/{$song->id}", [
            'title' => 'New Title',
            'artist_id' => $artist->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('songs', ['id' => $song->id, 'title' => 'New Title']);
    }

    public function test_user_cannot_update_other_song(): void
    {
        $otherUser = User::factory()->create();
        $artist = Artist::factory()->create();
        $song = $otherUser->songs()->create([
            'title' => 'Other Song',
            'artist_id' => $artist->id,
            'path' => 'songs/test.mp3',
            'duration' => 180,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->putJson("/api/v1/songs/{$song->id}", [
            'title' => 'Hacked Title',
            'artist_id' => $artist->id,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_song(): void
    {
        $artist = Artist::factory()->create();
        $song = $this->user->songs()->create([
            'title' => 'Delete Me',
            'artist_id' => $artist->id,
            'path' => 'songs/test.mp3',
            'duration' => 180,
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->deleteJson("/api/v1/songs/{$song->id}");

        $response->assertOk();
        $this->assertSoftDeleted('songs', ['id' => $song->id]);
    }
}
