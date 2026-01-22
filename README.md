# Laravel API – Songs & Playlists

## معرفی پروژه

این پروژه یک RESTful API مبتنی بر **Laravel** است که با استفاده از **JWT Authentication** پیاده‌سازی شده و امکان مدیریت آهنگ‌ها و پلی‌لیست‌ها را فراهم می‌کند.

پروژه برای استفاده در اپلیکیشن موبایل یا فرانت‌اند SPA طراحی شده است.

---

## Tech Stack

- PHP >= 8.1
- Laravel 10+
- MySQL / MariaDB
- JWT Authentication (`tymon/jwt-auth`)
- REST API (JSON)

---

## نصب و راه‌اندازی

### 1. نصب وابستگی‌ها

```bash
composer install
2. ساخت فایل environment
bash
Copy code
cp .env.example .env
تنظیم دیتابیس در .env:

env
Copy code
DB_DATABASE=database_name
DB_USERNAME=username
DB_PASSWORD=password
3. تولید App Key
bash
Copy code
php artisan key:generate
4. اجرای مایگریشن‌ها
bash
Copy code
php artisan migrate
5. لینک کردن Storage
bash
Copy code
php artisan storage:link
احراز هویت (Authentication)
POST /api/login
ورود کاربر و دریافت توکن JWT

Request

json
Copy code
{
  "email": "user@example.com",
  "password": "password"
}
Response

json
Copy code
{
  "access_token": "jwt_token_here",
  "token_type": "bearer",
  "expires_in": 3600
}
POST /api/register
ثبت‌نام کاربر جدید

Request

json
Copy code
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}
POST /api/refresh
تمدید توکن

Header

css
Copy code
Authorization: Bearer {token}
POST /api/logout
خروج کاربر

Header

css
Copy code
Authorization: Bearer {token}
API Versioning
bash
Copy code
Base URL: /api/v1
Protected Routes
تمام APIهای زیر نیاز به هدر احراز هویت دارند:

css
Copy code
Authorization: Bearer {token}
Songs API
Method	Endpoint
GET	/api/v1/songs
POST	/api/v1/songs
GET	/api/v1/songs/{id}
PATCH	/api/v1/songs/{id}
DELETE	/api/v1/songs/{id}

Playlists API
Method	Endpoint
GET	/api/v1/playlists
POST	/api/v1/playlists
GET	/api/v1/playlists/{id}
PATCH	/api/v1/playlists/{id}
DELETE	/api/v1/playlists/{id}

Playlist Songs
Method	Endpoint
POST	/api/v1/playlists/{playlistId}/songs
PATCH	/api/v1/playlists/{playlistId}/songs/reorder
DELETE	/api/v1/playlists/{playlistId}/songs/{songId}

Error Handling
API از HTTP Status Codeهای استاندارد استفاده می‌کند:

200 Success

401 Unauthorized

403 Forbidden

404 Not Found

422 Validation Error

500 Server Error

نمونه خطای ولیدیشن:

json
Copy code
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```
