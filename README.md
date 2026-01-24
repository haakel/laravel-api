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
```

### 2. ساخت فایل environment

```bash
cp .env.example .env
```

### تنظیم دیتابیس در .env:

```bash
DB_DATABASE=database_name
DB_USERNAME=username
DB_PASSWORD=password
```

### 3. تولید App Key

```bash
php artisan key:generate
```

### 4. اجرای مایگریشن‌ها

```bash
php artisan migrate --seed
```

### 5. لینک کردن Storage

```bash
php artisan storage:link
```

### 6. ساخت کلید jwt

```bash
php artisan jwt:secret
```

## API Account

### احراز هویت (Authentication)

### ورود

#### Request

`POST /api/login`

- ورود کاربر و دریافت توکن JWT

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

#### cURL Example

```bash
curl --location 'http://127.0.0.1:8000/api/register' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
    "email": "user@example.com",
    "password": "password"
}'
```

#### Response

```json
{
    "access_token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

### ثبت نام

#### Request

`POST /api/register`

- ثبت‌ نام کاربر جدید

```json
{
    "name": "name",
    "email": "1243234@test.com",
    "password": "password",
    "password_confirmation": "password"
}
```

#### cURL Example

```bash
curl --location 'http://127.0.0.1:8000/api/register' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
    "name": "name",
    "email": "1243234@test.com",
    "password": "password",
    "password_confirmation": "password"
}'
```

#### Response

```json
{
    "access_token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### Erorr

```json
{
    "message": "",
    "errors": {
        "email": [""],
        "password": [""]
    }
}
```

---

### تمدید توکن

#### Request

`POST /api/refresh`

- تمدید توکن

#### cURL Example

```bash
curl --location 'http://127.0.0.1:8000/api/refresh' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {token}'
```

#### Response

```json
{
    "access_token": "jwt_token_here",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

### خروج کاربر

#### Request

`POST /api/logout`

- خروح کاربر و غیر فعال شدن توکن

#### cURL Example

```bash
curl --location --request POST 'http://127.0.0.1:8000/api/logout' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {token}'
```

#### Response

```json
{
    "message": "Successfully logged out"
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

## API Songs

```bash
Base URL: /api/v1
```

### دریافت آهنگ های کاربر

#### Request

`GET /api/v1/songs`**\*\*\*\***\*\*\*\***\*\*\*\***\*\*\***\*\*\*\***\*\*\*\***\*\*\*\***

- دریافت آهنگ های کاربر

#### cURL Example

```bash
curl --location --request POST 'http://127.0.0.1:8000/api/logout' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {token}'
```

#### Response

```json
{
    "data": {
        "id": ,
        "user_id": ,
        "title": "",
        "artist_id": ,
        "album": "",
        "year_id": ,
        "genre_id": ,
        "duration": ,
        "path": "",
        "cover_path": "",
        "plays":
    }
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

### اضافه کردن آهنگ

#### Request

`POST /api/v1/song`

- اضافه کردن آهنگ به کاربر

#### cURL Example

`Content-Type: multipart/form-data`

```bash
curl --location --request POST 'http://127.0.0.1:8000//api/v1/song' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {token}
 -F "title=Song From XAMPP PHP" \
  -F "artist_id=1" \
  -F "album=XAMPP Album" \
  -F "year_id=1" \
  -F "genre_id=1" \
  -F "duration=215" \
  -F "song_file=@Rihanna-Where-Have-You-Been.mp3;type=audio/mpeg" \
  -F "cover_file=@photo_2025-12-31_16-29-05.jpg;type=image/jpeg"'
```

#### Response

```json
{
    "http_code": 201,
    "response": {
        "data": {
            "id": ,
            "user_id": ,
            "title": "",
            "artist_id": "",
            "album": "",
            "year_id": "",
            "genre_id": "",
            "duration": ,
            "path": "",
            "cover_path": "",
            "plays":
        }
    }
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

### نمایش اطلاعات یک آهنگ

#### Request

`GET /api/v1/song/{id}`

- هنگام فراخوانی اطلاعات id مورد نظر نمایش داده میشه

#### cURL Example

```bash
curl --location 'http://127.0.0.1:8000/api/v1/song/{id}' \
--header 'Authorization: Bearer {token}' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json'
```

#### Response

```json
{
    "data": {
        "id": ,
        "user_id": ,
        "title": "",
        "artist_id": ,
        "album": "",
        "year_id": ,
        "genre_id": ,
        "duration": ,
        "path": "",
        "cover_path": "",
        "plays":
    }
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

### تغییر اطلاعات آهنگ

#### Request

`PATCH /api/v1/song/{id}`

- هنگام فراخوانی اطلاعات id مورد نظر نمایش داده میشه

#### cURL Example

```bash
curl --location --request PATCH 'http://127.0.0.1:8000/api/v1/song' \
--header 'Authorization: Bearer {token}' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "song_id": "",
  "title": "",
  "artist_id": "",
  "album": "",
  "year_id":"" ,
  "genre_id":"" ,
  "cover_file":""
}
'
```

#### Response

```json
{
    "data": {
        "id": ,
        "user_id": ,
        "title": "",
        "artist_id": ,
        "album": "",
        "year_id": ,
        "genre_id": ,
        "duration": ,
        "path": "",
        "cover_path": "",
        "plays":
    }
}
```

#### Erorr

```json
{
    "message": ""
}
```

---

### حذف آهنگ

#### Request

`PATCH /api/v1/song/{id}`

- هنگام فراخوانی آهنگ با id مورد نظر حذف میشود

#### cURL Example

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/song/{id}' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {roken}'
'
```

#### Response

```json
{
    "data": {
        "id": ,
        "user_id": ,
        "title": "",
        "artist_id": ,
        "album": "",
        "year_id": ,
        "genre_id": ,
        "duration": ,
        "path": "",
        "cover_path": "",
        "plays":
    }
}
```

#### Erorr

```json
{
    "message": ""
}
```

---
