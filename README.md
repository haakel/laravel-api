<!DOCTYPE html>

<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>Laravel API Project - README</title>
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            line-height: 1.9;
            background: #f7f7f7;
            color: #222;
            padding: 30px;
        }
        h1, h2, h3 {
            color: #1f2937;
        }
        pre {
            background: #111827;
            color: #e5e7eb;
            padding: 15px;
            overflow-x: auto;
            border-radius: 6px;
        }
        code {
            color: #22c55e;
        }
        section {
            background: #ffffff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .endpoint {
            background: #f1f5f9;
            padding: 10px;
            border-left: 4px solid #3b82f6;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<h1>Laravel API Project</h1>

<section>
    <h2>معرفی پروژه</h2>
    <p>
        این پروژه یک API مبتنی بر Laravel است که شامل احراز هویت JWT، مدیریت آهنگ‌ها و پلی‌لیست‌ها می‌باشد.
    </p>
</section>

<section>
    <h2>پیش‌نیازها</h2>
    <ul>
        <li>PHP >= 8.1</li>
        <li>Composer</li>
        <li>MySQL / MariaDB</li>
    </ul>
</section>

<section>
    <h2>مراحل راه‌اندازی پروژه</h2>

```
<h3>1. نصب وابستگی‌ها</h3>
<pre><code>composer install</code></pre>

<h3>2. ساخت فایل env</h3>
<pre><code>cp .env.example .env</code></pre>

<p>تنظیم دیتابیس در فایل <code>.env</code>:</p>
<pre><code>DB_DATABASE=database_name
```

DB_USERNAME=username
DB_PASSWORD=password</code></pre>

```
<h3>3. تولید APP KEY</h3>
<pre><code>php artisan key:generate</code></pre>

<h3>4. اجرای مایگریشن‌ها</h3>
<pre><code>php artisan migrate</code></pre>

<h3>5. لینک کردن storage</h3>
<pre><code>php artisan storage:link</code></pre>
```

</section>

<section>
    <h2>احراز هویت (Authentication)</h2>

```
<div class="endpoint">
    <strong>POST</strong> /api/login
    <pre><code>{
```

"email": "[user@example.com](mailto:user@example.com)",
"password": "password"
}</code></pre> </div>

```
<div class="endpoint">
    <strong>POST</strong> /api/register
    <pre><code>{
```

"name": "string",
"email": "string",
"password": "string",
"password_confirmation": "string"
}</code></pre> </div>

```
<div class="endpoint">
    <strong>POST</strong> /api/refresh
    <p>Header:</p>
    <pre><code>Authorization: Bearer {token}</code></pre>
</div>

<div class="endpoint">
    <strong>POST</strong> /api/logout
    <p>Header:</p>
    <pre><code>Authorization: Bearer {token}</code></pre>
</div>
```

</section>

<section>
    <h2>API Version v1</h2>
    <p>Base URL:</p>
    <pre><code>/api/v1</code></pre>

```
<div class="endpoint">
    <strong>POST</strong> /datasong (Public)
    <pre><code>// TODO: request parameters</code></pre>
    <pre><code>// TODO: response</code></pre>
</div>
```

</section>

<section>
    <h2>API های محافظت‌شده (JWT)</h2>
    <p>تمام درخواست‌ها باید شامل هدر زیر باشند:</p>
    <pre><code>Authorization: Bearer {token}</code></pre>
</section>

<section>
    <h2>Songs</h2>

```
<div class="endpoint"><strong>GET</strong> /songs</div>
<div class="endpoint"><strong>POST</strong> /song</div>
<div class="endpoint"><strong>GET</strong> /song/{id}</div>
<div class="endpoint"><strong>PATCH</strong> /song</div>
<div class="endpoint"><strong>DELETE</strong> /song/{id}</div>
```

</section>

<section>
    <h2>Playlists</h2>

```
<div class="endpoint"><strong>GET</strong> /playlists</div>
<div class="endpoint"><strong>POST</strong> /playlist</div>
<div class="endpoint"><strong>GET</strong> /playlist/{id}</div>
<div class="endpoint"><strong>PATCH</strong> /playlist/{id}</div>
<div class="endpoint"><strong>DELETE</strong> /playlist/{id}</div>
```

</section>

<section>
    <h2>Playlist Songs</h2>

```
<div class="endpoint"><strong>POST</strong> /playlist/{playlistId}/songs</div>
<div class="endpoint"><strong>PATCH</strong> /playlist/{playlistId}/songs/reorder</div>
<div class="endpoint"><strong>DELETE</strong> /playlist/{playlistId}/songs/{songId}</div>
```

</section>

<section>
    <h2>TODO</h2>
    <ul>
        <li>تکمیل پارامترهای ورودی و خروجی APIها</li>
        <li>اضافه کردن Error Responses</li>
        <li>اضافه کردن Swagger / OpenAPI</li>
    </ul>
</section>

</body>
</html>
