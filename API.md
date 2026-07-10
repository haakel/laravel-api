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
Analyzes an uploaded audio file and returns its embedded ID3/metadata tags.

```bash
curl -X POST http://localhost:8000/api/v1/songs/metadata \
  -F "song_file=@/path/to/song.mp3"
```

**Response:**
```json
{
    "title": "Song Title",
    "artist": "Artist Name",
    "album": "Album Name",
    "year": "2024",
    "genre": "Rock",
    "duration": 245,
    "bitrate": 320000
}
```

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

## 3. Playlists Routes (Authenticated)

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

## 4. Playlist Songs Routes (Authenticated)

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

## 5. Favorites Routes (Authenticated)

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
