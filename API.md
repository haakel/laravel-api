# API Documentation

Base URL: `http://localhost:8000/api/v1`

Authentication: **JWT Bearer Token**

---

## Response Format

### Success (Single Result)
```json
{
    "status": true,
    "message": "Success",
    "data": {}
}
```

### Success (Paginated)
```json
{
    "status": true,
    "message": "Success",
    "data": [],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Error
```json
{
    "status": false,
    "message": "Error description",
    "data": null,
    "errors": []
}
```

---

## 1. Auth Routes (Public)

### Register
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
    "access_token": "eyJ...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

### Refresh Token
```bash
curl -X POST http://localhost:8000/api/v1/refresh \
  -H "Authorization: Bearer <token>"
```

### Logout (Authenticated)
```bash
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer <token>"
```

---

## 2. Songs Routes

### List Songs (Authenticated)
Filter and paginate songs. All query parameters are optional.

```bash
curl -X GET "http://localhost:8000/api/v1/songs?title=love&artist_id=1&album=Greatest&genre_id=2&year_id=3" \
  -H "Authorization: Bearer <token>"
```

**Query Parameters:**

| Parameter   | Type   | Description                  |
|-------------|--------|------------------------------|
| `title`     | string | Search by title (LIKE)       |
| `artist_id` | int    | Filter by artist ID          |
| `album`     | string | Search by album name (LIKE)  |
| `genre_id`  | int    | Filter by genre ID           |
| `year_id`   | int    | Filter by year ID            |

---

### Search Songs (Public)
Dedicated search endpoint with the same filters.

```bash
curl -X GET "http://localhost:8000/api/v1/songs/search?title=love&artist_id=1" \
  -H "Authorization: Bearer <token>"
```

**Query Parameters:** Same as List Songs above.

---

### Create Song (Authenticated)
```bash
curl -X POST http://localhost:8000/api/v1/songs \
  -H "Authorization: Bearer <token>" \
  -F "title=My Song" \
  -F "artist_id=1" \
  -F "album=My Album" \
  -F "year_id=1" \
  -F "genre_id=1" \
  -F "song_file=@/path/to/song.mp3" \
  -F "cover_file=@/path/to/cover.jpg"
```

**Song file:** `mp3`, `wav`, `ogg`, `m4a`, `aac`, `mp4` — max 50MB
**Cover file:** `jpg`, `jpeg`, `png` — max 5MB

---

### Show Song (Authenticated)
```bash
curl -X GET http://localhost:8000/api/v1/songs/1 \
  -H "Authorization: Bearer <token>"
```

---

### Stream Song (Authenticated)
Returns the audio file as a stream. Supports `Accept-Ranges: bytes` for seek/scrubbing in players.

```bash
curl -X GET http://localhost:8000/api/v1/songs/1/stream \
  -H "Authorization: Bearer <token>"
```

**Note:** Each stream request increments the play count for the song.

---

### Update Song (Authenticated — Owner only)
```bash
curl -X PUT http://localhost:8000/api/v1/songs/1 \
  -H "Authorization: Bearer <token>" \
  -F "title=Updated Song" \
  -F "artist_id=1" \
  -F "cover_file=@/path/to/new-cover.jpg"
```

---

### Delete Song (Authenticated — Owner only)
```bash
curl -X DELETE http://localhost:8000/api/v1/songs/1 \
  -H "Authorization: Bearer <token>"
```

---

### Extract Metadata from Audio File (Public)
Analyzes an uploaded audio file and returns its embedded ID3/metadata tags,
plus enriched data from MusicBrainz and Deezer (cover art, ISRC, rank, etc.).

```bash
curl -X POST http://localhost:8000/api/v1/songs/metadata \
  -F "song_file=@/path/to/song.mp3"
```

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "metadata": {
            "title": "Song Title",
            "artist": "Artist Name",
            "album": "Album Name",
            "year": "2024",
            "genre": "Rock",
            "duration": 245,
            "bitrate": 320000
        },
        "music_brainz": {
            "title": "Song Title",
            "artist": "Artist Name",
            "album": "Album Name",
            "year": "2024",
            "cover": "https://coverartarchive.org/release/.../front"
        },
        "deezer": {
            "deezer_id": 3135556,
            "title": "Song Title",
            "artist": "Artist Name",
            "album": "Album Name",
            "duration": 225,
            "isrc": "USWB10800123",
            "preview_url": "https://cdns-preview-1.dzcdn.net/...mp3",
            "rank": 950000,
            "cover_xl": "https://e-cdns-images.dzcdn.net/images/cover/.../1000x1000-000000-80-0-0.jpg"
        }
    }
}
```

> **Note:** `music_brainz` and `deezer` are empty objects `{}` when no match is found.

---

### Search MusicBrainz (Public)
Searches the MusicBrainz database for recording metadata.

```bash
curl -X POST http://localhost:8000/api/v1/songs/search-musicbrainz \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Bohemian Rhapsody",
    "artist": "Queen"
  }'
```

---

## 3. Deezer Routes (Public)

Search tracks, albums, and metadata via the Deezer API. No authentication required.

### Search Deezer
Search tracks by query string (e.g. `artist + track`).

```bash
curl -X GET "http://localhost:8000/api/v1/songs/search-deezer?q=queen+bohemian+rhapsody"
```

**Query Parameters:**

| Parameter | Type   | Required | Description                     |
|-----------|--------|----------|---------------------------------|
| `q`       | string | Yes      | Search query (artist + track)   |

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "count": 5,
        "results": [
            {
                "deezer_id": 3135556,
                "title": "Bohemian Rhapsody",
                "artist": "Queen",
                "artist_id": 412,
                "album": "A Night At The Opera",
                "album_id": 302127,
                "duration": 354,
                "isrc": "GBUM71029604",
                "preview_url": "https://cdns-preview-1.dzcdn.net/...mp3",
                "rank": 950000,
                "cover_small": "https://e-cdns-images.dzcdn.net/images/cover/.../56x56-000000-80-0-0.jpg",
                "cover_medium": "https://e-cdns-images.dzcdn.net/images/cover/.../250x250-000000-80-0-0.jpg",
                "cover_big": "https://e-cdns-images.dzcdn.net/images/cover/.../500x500-000000-80-0-0.jpg",
                "cover_xl": "https://e-cdns-images.dzcdn.net/images/cover/.../1000x1000-000000-80-0-0.jpg"
            }
        ]
    }
}
```

---

### Search Deezer by ISRC
Find a track on Deezer using its ISRC (International Standard Recording Code).

```bash
curl -X GET "http://localhost:8000/api/v1/songs/search-deezer-isrc?isrc=GBUM71029604"
```

**Query Parameters:**

| Parameter | Type   | Required | Description                          |
|-----------|--------|----------|--------------------------------------|
| `isrc`    | string | Yes      | ISRC code (e.g. `USWB10800123`)     |

**Response:** Same shape as a single item in `search-deezer` results.

---

### Get Deezer Album
Retrieve full album info + track list from Deezer.

```bash
curl -X GET http://localhost:8000/api/v1/songs/deezer-album/302127
```

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 302127,
        "title": "A Night At The Opera",
        "artist": "Queen",
        "cover_big": "https://...",
        "cover_xl": "https://...",
        "release_date": "1975-11-21",
        "track_count": 12,
        "label": "Hollywood Records",
        "tracks": [
            {
                "deezer_id": 3135556,
                "title": "Bohemian Rhapsody",
                "artist": "Queen",
                "duration": 354,
                "isrc": "GBUM71029604"
            }
        ]
    }
}
```

---

## 4. Song.link Routes (Public)

Cross-platform track linking. Given a URL from Spotify, YouTube, Deezer, Tidal, etc., returns links to the same track on all supported platforms.

**Supported platforms:** Spotify, Deezer, YouTube, Tidal, Amazon Music, SoundCloud, Apple Music

### Get Cross-platform Links by URL
```bash
curl -X POST http://localhost:8000/api/v1/songs/songlink \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC"
  }'
```

**Request Body:**

| Field | Type   | Required | Description                                    |
|-------|--------|----------|------------------------------------------------|
| `url` | string | Yes      | Any track URL (Spotify, YouTube, Deezer, etc.) |

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "title": "Bohemian Rhapsody",
        "artist": "Queen",
        "thumbnail": "https://i.scdn.co/image/...",
        "thumbnail_width": 640,
        "duration": 354,
        "platforms": {
            "spotify": "https://open.spotify.com/track/4uLU6...",
            "deezer": "https://deezer.com/track/3135556",
            "youtube": "https://youtube.com/watch?v=fJ9rUzIMcZQ",
            "tidal": "https://tidal.com/browse/track/...",
            "amazon_music": "https://music.amazon.com/track/...",
            "soundcloud": "https://soundcloud.com/...",
            "apple_music": "https://music.apple.com/track/..."
        },
        "entity_unique_id": "SPOTIFY::track::4uLU6..."
    }
}
```

> **Note:** Not all platforms are available for every track. Missing platforms return `null`.

---

### Get Cross-platform Links by ISRC
```bash
curl -X GET "http://localhost:8000/api/v1/songs/songlink-isrc?isrc=USWB10800123"
```

**Query Parameters:**

| Parameter | Type   | Required | Description                     |
|-----------|--------|----------|---------------------------------|
| `isrc`    | string | Yes      | ISRC code                       |

**Response:** Same shape as `songlink` response above.

---

## 5. Lyrics Routes (Public)

Fetch synced lyrics (with timestamps) via the LRCLib API. No authentication required.

### Get Lyrics
```bash
curl -X GET "http://localhost:8000/api/v1/songs/lyrics?track=Bohemian+Rhapsody&artist=Queen&duration=354&album=A+Night+At+The+Opera"
```

**Query Parameters:**

| Parameter | Type   | Required | Description                               |
|-----------|--------|----------|-------------------------------------------|
| `track`   | string | Yes      | Track name                                |
| `artist`  | string | Yes      | Artist name                               |
| `duration`| int    | Yes      | Duration in seconds (for accurate matching)|
| `album`   | string | No       | Album name (optional, improves accuracy)  |

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "id": 12345,
        "track_name": "Bohemian Rhapsody",
        "artist_name": "Queen",
        "album_name": "A Night At The Opera",
        "duration": 354,
        "instrumental": false,
        "synced_lyrics": "[00:00.00]Is this the real life...\n[00:03.50]Is this just fantasy...",
        "plain_lyrics": "Is this the real life...\nIs this just fantasy...",
        "parsed_lyrics": [
            { "time": 0.0, "text": "Is this the real life..." },
            { "time": 3.5, "text": "Is this just fantasy..." }
        ]
    }
}
```

**Fields:**
- `synced_lyrics` — LRC format with timestamps (`[MM:SS.xx]text`)
- `plain_lyrics` — Plain text without timestamps
- `parsed_lyrics` — Pre-parsed array of `{time, text}` for player integration

---

### Search Lyrics
Search lyrics by free-text query.

```bash
curl -X GET "http://localhost:8000/api/v1/songs/lyrics/search?q=bohemian+rhapsody+queen"
```

**Query Parameters:**

| Parameter | Type   | Required | Description        |
|-----------|--------|----------|--------------------|
| `q`       | string | Yes      | Search query       |

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "count": 3,
        "results": [
            {
                "id": 12345,
                "track_name": "Bohemian Rhapsody",
                "artist_name": "Queen",
                "album_name": "A Night At The Opera",
                "duration": 354,
                "instrumental": false,
                "synced_lyrics": "...",
                "plain_lyrics": "...",
                "parsed_lyrics": [...]
            }
        ]
    }
}
```

---

## 6. Playlists Routes (Authenticated)

### List Playlists
```bash
curl -X GET http://localhost:8000/api/v1/playlists \
  -H "Authorization: Bearer <token>"
```

### Create Playlist
```bash
curl -X POST http://localhost:8000/api/v1/playlists \
  -H "Authorization: Bearer <token>" \
  -F "name=My Playlist" \
  -F "description=A great playlist" \
  -F "is_public=true" \
  -F "song_ids[]=1" \
  -F "song_ids[]=2" \
  -F "cover=@/path/to/cover.jpg"
```

### Show Playlist
```bash
curl -X GET http://localhost:8000/api/v1/playlists/1 \
  -H "Authorization: Bearer <token>"
```

### Update Playlist
```bash
curl -X PUT http://localhost:8000/api/v1/playlists/1 \
  -H "Authorization: Bearer <token>" \
  -F "name=Updated Playlist" \
  -F "description=Updated description" \
  -F "is_public=false" \
  -F "song_ids[]=1" \
  -F "song_ids[]=3" \
  -F "cover=@/path/to/new-cover.jpg"
```

### Delete Playlist
```bash
curl -X DELETE http://localhost:8000/api/v1/playlists/1 \
  -H "Authorization: Bearer <token>"
```

---

## 7. Playlist Songs Routes (Authenticated)

### Add Song to Playlist
```bash
curl -X POST http://localhost:8000/api/v1/playlists/1/songs \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "song_id": 1
  }'
```

### Reorder Songs in Playlist
```bash
curl -X PATCH http://localhost:8000/api/v1/playlists/1/songs/reorder \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "song_ids": [3, 1, 2, 5]
  }'
```

### Remove Song from Playlist
```bash
curl -X DELETE http://localhost:8000/api/v1/playlists/1/songs/1 \
  -H "Authorization: Bearer <token>"
```

---

## 8. Favorites Routes (Authenticated)

### List Favorites
```bash
curl -X GET http://localhost:8000/api/v1/favorites \
  -H "Authorization: Bearer <token>"
```

### Add to Favorites
```bash
curl -X POST http://localhost:8000/api/v1/favorites \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "song_id": 1
  }'
```

### Remove from Favorites
```bash
curl -X DELETE http://localhost:8000/api/v1/favorites \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "song_id": 1
  }'
```

---

## Song Resource Shape

```json
{
    "id": 1,
    "user_id": 1,
    "title": "My Song",
    "artist": { "id": 1, "name": "Artist Name" },
    "genre": { "id": 2, "name": "Rock" },
    "year": 2024,
    "album": "Album Name",
    "duration": 245,
    "cover_url": "http://localhost:8000/storage/covers/...jpg",
    "plays": 42,
    "created_at": "2026-07-09T12:00:00.000000Z",
    "updated_at": "2026-07-09T12:00:00.000000Z"
}
```

> **Note:** If `cover_path` is null, `cover_url` returns `/images/default-cover.jpg`.

## Playlist Resource Shape

```json
{
    "id": 1,
    "name": "My Playlist",
    "description": "A great playlist",
    "is_public": true,
    "cover_url": "http://localhost:8000/storage/playlist-covers/...jpg",
    "songs_count": 5,
    "songs": [ ... ],
    "created_at": "...",
    "updated_at": "..."
}
```

---

## Error Codes

| HTTP Code | Meaning                   |
|-----------|---------------------------|
| 200       | Success                   |
| 201       | Created                   |
| 400       | Bad Request / Validation  |
| 401       | Unauthenticated           |
| 403       | Forbidden (not owner)     |
| 404       | Resource not found        |
| 422       | Validation Error          |
