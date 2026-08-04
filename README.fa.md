<div align="center">

[🇬🇧 English](README.md) | [🇮🇷 فارسی](#)

---

# 🎵 API مدیریت موزیک

**یک API قدرتمند برای مدیریت موزیک، پلی‌لیست‌ها و علاقه‌مندی‌ها — ساخته شده با Laravel**

ساخته شده با ❤️ توسط **حمید اکبری** 

</div>

---

## ✨ قابلیت‌ها

- 🔐 **احراز هویت JWT** — ثبت‌نام، ورود، تمدید و خروج
- 🎶 **مدیریت آهنگ‌ها** — آپلود، پخش (stream)، جستجو، فیلتر، استخراج متادیتا
- 📂 **مدیریت پلی‌لیست‌ها** — ساخت، مرتب‌سازی مجدد، عمومی/خصوصی
- ❤️ **علاقه‌مندی‌ها** — افزودن/حذف آهنگ‌های مورد علاقه
- 🧠 **یکپارچگی با MusicBrainz** — جستجوی خودکار اطلاعات آهنگ
- 🎵 **یکپارچگی با Deezer** — جستجوی آهنگ، اطلاعات آلبوم، کاور آرت، جستجو با ISRC
- 🔗 **Song.link چندپلتفرمی** — پیدا کردن همان آهنگ در Spotify, YouTube, Tidal, Deezer و غیره
- 📝 **لیریکس همزمان (LrcLib)** — دریافت لیریکس با timestamp برای نمایش در پخش‌کننده
- 🖼️ **کاور پیش‌فرض** — در صورت نبود کاور، عکس پیش‌فرض نمایش داده می‌شود
- 🔍 **جستجو و فیلتر** — جستجو بر اساس عنوان، آرتیست، آلبوم، ژانر و سال
- 📦 **Spatie Media Library** — مدیریت هوشمند فایل‌ها
- 👮 **سیاست‌های دسترسی** — هر کاربر فقط به محتوای خودش دسترسی دارد

---

## 🛠 تکنولوژی‌ها

| تکنولوژی | نسخه |
|------|---------|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| MySQL / MariaDB | — |
| JWT Auth | `tymon/jwt-auth` ^2.2 |
| پردازش صدا | `james-heinrich/getid3` ^1.9 |
| مدیریت فایل | `spatie/laravel-medialibrary` ^11.0 |
| پردازش تصویر | `intervention/image` ^3.11 |
| تست | Pest PHP ^4.2 |

---

## 🚀 شروع سریع

### ۱. نصب وابستگی‌ها

```bash
git clone <repo-url>
cd laravel-api
composer install
npm install && npm run build
```

### ۲. تنظیم محیط

```bash
cp .env.example .env
```

دیتابیس خود را در `.env` تنظیم کنید:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=
```

### ۳. تولید کلیدها و اجرای مایگریشن

```bash
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
php artisan storage:link
```

### ۴. اجرا

```bash
php artisan serve
```

---

## 🔑 حساب‌های تستی

بعد از اجرای سیدر:

| ایمیل | رمز عبور |
|-------|----------|
| `hamid@example.com` | `password` |
| `bob@example.com` | `password123` |

---

## 📡 لیست APIها

Base URL: `http://localhost:8000/api/v1`

### احراز هویت (عمومی)

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `POST` | `/register` | ثبت‌نام کاربر جدید |
| `POST` | `/login` | ورود و دریافت توکن JWT |
| `POST` | `/refresh` | تمدید توکن |
| `POST` | `/logout` | خروج و باطل کردن توکن |

### آهنگ‌ها

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `GET` | `/songs` | لیست آهنگ‌ها (فیلتر: `?title=&artist_id=&album=&genre_id=&year_id=`) |
| `GET` | `/songs/search` | جستجوی آهنگ |
| `POST` | `/songs` | آپلود آهنگ جدید (`multipart/form-data`) |
| `GET` | `/songs/{id}` | نمایش جزئیات آهنگ |
| `GET` | `/songs/{id}/stream` | پخش آنلاین آهنگ |
| `PUT` | `/songs/{id}` | ویرایش آهنگ |
| `DELETE` | `/songs/{id}` | حذف آهنگ |
| `POST` | `/songs/metadata` | استخراج متادیتا + اطلاعات Deezer و MusicBrainz |
| `POST` | `/songs/search-musicbrainz` | جستجو در MusicBrainz |

### Deezer (عمومی)

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `GET` | `/songs/search-deezer?q=` | جستجوی آهنگ در Deezer |
| `GET` | `/songs/search-deezer-isrc?isrc=` | پیدا کردن آهنگ با کد ISRC |
| `GET` | `/songs/deezer-album/{id}` | دریافت اطلاعات آلبوم + لیست آهنگ‌ها |

### Song.link — چندپلتفرمی (عمومی)

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `POST` | `/songs/songlink` | پیدا کردن لینک در Spotify/YouTube/Tidal با URL |
| `GET` | `/songs/songlink-isrc?isrc=` | پیدا کردن لینک با کد ISRC |

### لیریکس — LrcLib (عمومی)

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `GET` | `/songs/lyrics?track=&artist=&duration=&album=` | دریافت لیریکس همزمان |
| `GET` | `/songs/lyrics/search?q=` | جستجوی لیریکس |

### پلی‌لیست‌ها

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `GET` | `/playlists` | لیست پلی‌لیست‌های کاربر |
| `POST` | `/playlists` | ساخت پلی‌لیست جدید |
| `GET` | `/playlists/{id}` | مشاهده پلی‌لیست |
| `PUT` | `/playlists/{id}` | ویرایش پلی‌لیست |
| `DELETE` | `/playlists/{id}` | حذف پلی‌لیست |

### آهنگ‌های پلی‌لیست

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `POST` | `/playlists/{id}/songs` | افزودن آهنگ به پلی‌لیست |
| `PATCH` | `/playlists/{id}/songs/reorder` | مرتب‌سازی مجدد آهنگ‌ها |
| `DELETE` | `/playlists/{id}/songs/{songId}` | حذف آهنگ از پلی‌لیست |

### علاقه‌مندی‌ها

| متد | آدرس | توضیح |
|--------|----------|-------------|
| `GET` | `/favorites` | لیست آهنگ‌های مورد علاقه |
| `POST` | `/favorites` | افزودن به علاقه‌مندی‌ها |
| `DELETE` | `/favorites` | حذف از علاقه‌مندی‌ها |

---

## 📦 محدودیت آپلود

| نوع فایل | حداکثر حجم | فرمت‌های مجاز |
|-----------|----------|-----------------|
| فایل صوتی | **۵۰ مگابایت** | `mp3`, `wav`, `ogg`, `m4a`, `aac`, `mp4` |
| کاور | **۵ مگابایت** | `jpg`, `jpeg`, `png` |

---

## 📬 ساختار پاسخ‌ها

### موفقیت
```json
{
    "status": true,
    "message": "Success",
    "data": {}
}
```

### صفحه‌بندی شده
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

### خطا
```json
{
    "status": false,
    "message": "Error description",
    "data": null,
    "errors": []
}
```

---

## 🐛 مشکلات رایج

### آپلود با خطا مواجه می‌شود
محدودیت آپلود PHP را در `php.ini` افزایش دهید:

```ini
upload_max_filesize = 64M
post_max_size = 64M
```

سپس Apache یا `php artisan serve` را ریستارت کنید.

---

## 🧪 اجرای تست‌ها

```bash
php artisan test
```

نیاز به دیتابیس `laravel_api_test` دارد (با `migrate:fresh` خودکار ساخته می‌شود).

---

## 📁 ساختار پروژه

```
app/
├── Http/
│   ├── Controllers/api/    # کنترلرهای API
│   ├── Requests/           # اعتبارسنجی ورودی‌ها
│   ├── Resources/          # تبدیل داده‌های API
│   └── Traits/ApiResponse  # پاسخ‌های یکسان
├── Models/                 # مدل‌های Eloquent
├── Policies/               # سیاست‌های دسترسی
├── Repositories/           # لایه دسترسی به داده
└── Services/               # لایه منطق کسب و کار
database/
└── migrations/             # مایگریشن‌های دیتابیس
    seeders/                # دیتاهای اولیه
```

---

## 📄 مجوز

MIT — آزاد برای استفاده، تغییر و انتشار.

---

<div align="center">
ساخته شده با 🎵 توسط <a href="https://tunetales.ir">tunetales</a>
</div>
