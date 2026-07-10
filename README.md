<div align="center">

[🇬🇧 English](#) | [🇮🇷 فارسی](README.fa.md)

---

# 🎵 Laravel Music API

**A RESTful music management API built with Laravel — JWT authentication, song/playlist management, streaming, and MusicBrainz integration.**

Built with ❤️ by **Hamid Akbari** — [melipayamak.com](https://melipayamak.com)

</div>

---

## ✨ Features

- 🔐 **JWT Authentication** — register, login, refresh, logout
- 🎶 **Song Management** — upload, stream, search, filter, metadata extraction
- 📂 **Playlist Management** — create, reorder, public/private
- ❤️ **Favorites** — add/remove favorite songs
- 🧠 **MusicBrainz Integration** — auto-tagging from audio fingerprint
- 🖼️ **Default Cover** — automatic fallback when no cover uploaded
- 🔍 **Search & Filters** — filter songs by title, artist, album, genre, year
- 📦 **Spatie Media Library** — file management with conversions
- 👮 **Authorization Policies** — per-user ownership enforcement

---

## 🛠 Tech Stack

| Tech | Version |
|------|---------|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| MySQL / MariaDB | — |
| JWT Auth | `tymon/jwt-auth` ^2.2 |
| Audio Parsing | `james-heinrich/getid3` ^1.9 |
| Media Library | `spatie/laravel-medialibrary` ^11.0 |
| Image Processing | `intervention/image` ^3.11 |
| Testing | Pest PHP ^4.2 |

---

## 🚀 Quick Start

### 1. Clone & Install

```bash
git clone <repo-url>
cd laravel-api
composer install
npm install && npm run build
```

### 2. Environment Setup

```bash
cp .env.example .env
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate Keys & Migrate

```bash
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
php artisan storage:link
```

### 4. Serve

```bash
php artisan serve
```

---

## 🔑 Default Test Account

After running seeder:

| Email | Password |
|-------|----------|
| `hamid@example.com` | `password` |
| `bob@example.com` | `password123` |

---

## 📡 API Endpoints

Base URL: `http://localhost:8000/api/v1`

### Auth (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/register` | Register new user |
| `POST` | `/login` | Login & get JWT token |
| `POST` | `/refresh` | Refresh JWT token |
| `POST` | `/logout` | Invalidate token (auth) |

### Songs

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/songs` | List songs (filters: `?title=&artist_id=&album=&genre_id=&year_id=`) |
| `GET` | `/songs/search` | Search songs (same filters) |
| `POST` | `/songs` | Upload new song (`multipart/form-data`) |
| `GET` | `/songs/{id}` | Show song details |
| `GET` | `/songs/{id}/stream` | Stream audio file |
| `PUT` | `/songs/{id}` | Update song info |
| `DELETE` | `/songs/{id}` | Delete song |
| `POST` | `/songs/metadata` | Extract metadata from audio file |
| `POST` | `/songs/search-musicbrainz` | Search MusicBrainz database |

### Playlists

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/playlists` | List user playlists |
| `POST` | `/playlists` | Create playlist |
| `GET` | `/playlists/{id}` | Show playlist |
| `PUT` | `/playlists/{id}` | Update playlist |
| `DELETE` | `/playlists/{id}` | Delete playlist |

### Playlist Songs

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/playlists/{id}/songs` | Add song to playlist |
| `PATCH` | `/playlists/{id}/songs/reorder` | Reorder songs |
| `DELETE` | `/playlists/{id}/songs/{songId}` | Remove song from playlist |

### Favorites

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/favorites` | List favorite songs |
| `POST` | `/favorites` | Add song to favorites |
| `DELETE` | `/favorites` | Remove song from favorites |

---

## 📦 Upload Limits

| File Type | Max Size | Allowed Formats |
|-----------|----------|-----------------|
| Song file | **50 MB** | `mp3`, `wav`, `ogg`, `m4a`, `aac`, `mp4` |
| Cover image | **5 MB** | `jpg`, `jpeg`, `png` |

---

## 📬 Response Format

### Success
```json
{
    "status": true,
    "message": "Success",
    "data": {}
}
```

### Paginated
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

## 🐛 Common Issues

### Upload fails with "song file failed to upload"
Check PHP upload limits in `php.ini`:

```ini
upload_max_filesize = 64M
post_max_size = 64M
```

Then restart Apache / `php artisan serve`.

---

## 🧪 Running Tests

```bash
php artisan test
```

Requires `laravel_api_test` database (created automatically via `migrate:fresh`).

---

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/api/    # API Controllers
│   ├── Requests/           # Form Requests (validation)
│   ├── Resources/          # API Resource transformers
│   └── Traits/ApiResponse  # Unified response trait
├── Models/                 # Eloquent models
├── Policies/               # Authorization policies
├── Repositories/           # Data access layer
└── Services/               # Business logic layer
database/
└── migrations/             # Schema migrations
    seeders/                # Database seeders
```

---

## 📄 License

MIT — free to use, modify, and distribute.

---

<div align="center">
Made with 🎵 by <a href="https://melipayamak.com">Meli Payamak</a>
</div>
