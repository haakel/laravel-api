<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس دریافت لیریکس همزمان (Synced Lyrics) از LRCLib
 *
 * LRCLib یک API رایگان و اوپن‌سورس برای دریافت لیریکس آهنگ‌هاست.
 * ویژگی اصلی آن پشتیبانی از لیریکس همزمان (با timestamp) است که برای
 * نمایش لیریکس متحرک در پخش‌کننده موزیک بسیار مفید است.
 *
 * فرمت خروجی:
 *   - synced_lyrics: لیریکس با فرمت LRC "[00:12.34]متن"
 *   - plain_lyrics:  لیریکس ساده بدون timestamp
 *   - parsed_lyrics: آرایه پارس شده [{time: 12.34, text: "متن"}, ...]
 *
 * API Documentation: https://lrclib.net/docs
 */
class LrcLibService
{
    /**
     * آدرس پایه LRCLib API
     */
    protected string $baseUrl = 'https://api.lrclib.net';

    /**
     * ساخت کلاینت HTTP با تنظیمات مشترک
     *
     * - تایم‌اوت ۱۰ ثانیه‌ای
     * - در محیط لوکال (API_SSL_VERIFY=false) گواهی SSL غیرفعال می‌شود
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(10);

        // غیرفعال کردن SSL verify در محیط توسعه لوکال
        // روی سرور تولید این شرط هیچوقت اجرا نمی‌شود
        if (!config('services_api.ssl_verify')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * دریافت لیریکس با عنوان آهنگ، هنرمند و مدت زمان
     *
     * پارامتر duration برای تطابق دقیق‌تر ضروری است — چون آهنگ‌های مختلف
     * ممکن است عنوان یکسان داشته باشند ولی مدت زمانشان متفاوت است.
     *
     * @param string $trackName       عنوان آهنگ
     * @param string $artistName      نام هنرمند
     * @param int $duration           مدت زمان آهنگ به ثانیه
     * @param string|null $albumName  نام آلبوم (اختیاری، دقت را بالا می‌برد)
     * @return array|null             اطلاعات لیریکس یا null در صورت پیدا نشدن
     */
    public function getLyrics(
        string $trackName,
        string $artistName,
        int $duration,
        ?string $albumName = null
    ): ?array {
        try {
            $params = [
                'track_name' => $trackName,
                'artist_name' => $artistName,
                'duration' => $duration,
            ];

            // اضافه کردن نام آلبوم در صورت وجود (بهبود دقت تطابق)
            if ($albumName) {
                $params['album_name'] = $albumName;
            }

            $response = $this->http()
                ->get("{$this->baseUrl}/get", $params);

            if ($response->failed()) {
                Log::error('LrcLib API failed', [
                    'status' => $response->status(),
                    'track' => $trackName,
                    'artist' => $artistName,
                ]);
                return null;
            }

            $data = $response->json();

            // اگر پاسخ خالی بود یا trackName نداشت، لیریکسی پیدا نشده
            if (empty($data['trackName'])) {
                return null;
            }

            return $this->normalizeResponse($data);
        } catch (\Exception $e) {
            Log::error('LrcLib connection error', [
                'error' => $e->getMessage(),
                'track' => $trackName,
                'artist' => $artistName,
            ]);
            return null;
        }
    }

    /**
     * جستجوی لیریکس با عبارت آزاد
     *
     * از این متد می‌توان برای جستجوی لیریکس با هر عبارتی استفاده کرد.
     * نتایج شامل لیریکس‌های متعدد خواهد بود.
     *
     * @param string $query  عبارت جستجو (مثلاً "Bohemian Rhapsody Queen")
     * @return array         آرایه‌ای از لیریکس‌های پیدا شده
     */
    public function search(string $query): array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/search", [
                    'q' => $query,
                ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();

            if (empty($data)) {
                return [];
            }

            // نرمال‌سازی تمام نتایج
            return collect($data)->map(function ($item) {
                return $this->normalizeResponse($item);
            })->toArray();
        } catch (\Exception $e) {
            Log::error('LrcLib search error', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    /**
     * دریافت لیریکس با شناسه عددی LRCLib
     *
     * @param int $id  شناسه لیریکس در LRCLib
     * @return array|null  اطلاعات لیریکس یا null
     */
    public function getById(int $id): ?array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/get/{$id}");

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            return $this->normalizeResponse($data);
        } catch (\Exception $e) {
            Log::error('LrcLib get by ID error', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
            return null;
        }
    }

    /**
     * بررسی وجود لیریکس برای یک آهنگ
     *
     * فقط بررسی می‌کند آیا لیریکسی موجود است یا نه (بدون دریافت محتوا).
     * مفید برای نمایش آیکون "لیریکس موجود است" در UI.
     *
     * @param string $trackName       عنوان آهنگ
     * @param string $artistName      نام هنرمند
     * @param int $duration           مدت زمان به ثانیه
     * @param string|null $albumName  نام آلبوم (اختیاری)
     * @return bool                   true اگر لیریکس موجود باشد
     */
    public function exists(
        string $trackName,
        string $artistName,
        int $duration,
        ?string $albumName = null
    ): bool {
        $result = $this->getLyrics($trackName, $artistName, $duration, $albumName);
        return $result !== null;
    }

    /**
     * پارس فرمت LRC (لیریکس همزمان) به آرایه با timestamp
     *
     * فرمت LRC استاندارد جهانی برای لیریکس همزمان است:
     *   [00:12.34]Hello World
     *   [00:15.67]Goodbye
     *
     * خروجی پارس شده:
     *   [
     *       ['time' => 12.34, 'text' => 'Hello World'],
     *       ['time' => 15.67, 'text' => 'Goodbye'],
     *   ]
     *
     * این فرمت برای پیاده‌سازی لیریکس متحرک در پخش‌کننده موزیک ایده‌آل است.
     *
     * @param string $lrcContent  محتوای لیریکس با فرمت LRC
     * @return array               آرایه پارس شده [{time, text}, ...]
     */
    public function parseSyncedLyrics(string $lrcContent): array
    {
        $lines = explode("\n", $lrcContent);
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // الگوی regex برای فرمت LRC: [MM:SS.xx]متن
            // پشتیبانی از ۲ یا ۳ رقم اعشار
            if (preg_match('/\[(\d{2}):(\d{2})\.(\d{2,3})\](.*)/', $line, $matches)) {
                $minutes = (int) $matches[1];
                $seconds = (int) $matches[2];
                $fraction = (float) ('0.' . $matches[3]);
                $text = trim($matches[4]);

                // تبدیل دقیقه:ثانیه به ثانیه خالص
                $time = ($minutes * 60) + $seconds + $fraction;

                $result[] = [
                    'time' => round($time, 3), // ۳ رقم اعشار (میلی‌ثانیه)
                    'text' => $text,
                ];
            }
        }

        return $result;
    }

    /**
     * نرمال‌سازی پاسخ خام LRCLib API به فرمت استاندارد پروژه
     *
     * LRCLib سه نوع خروجی دارد:
     *   - syncedLyrics: لیریکس همزمان با فرمت LRC (بهترین برای پخش‌کننده)
     *   - plainLyrics: لیریکس ساده بدون timestamp
     *   - instrumental: آیا آهنگ بدون کلام است
     *
     * @param array $data  پاسخ خام از LRCLib API
     * @return array       داده نرمال‌سازی شده
     */
    protected function normalizeResponse(array $data): array
    {
        return [
            'id' => $data['id'] ?? null,
            'track_name' => $data['trackName'] ?? '',
            'artist_name' => $data['artistName'] ?? '',
            'album_name' => $data['albumName'] ?? '',
            'duration' => $data['duration'] ?? 0,
            'instrumental' => $data['instrumental'] ?? false, // آیا بدون کلام است؟

            // لیریکس همزمان (فرمت LRC) — برای نمایش متحرک
            'synced_lyrics' => $data['syncedLyrics'] ?? null,

            // لیریکس ساده (متن خالص) — برای نمایش ثابت
            'plain_lyrics' => $data['plainLyrics'] ?? null,

            // لیریکس پارس شده — آماده استفاده مستقیم در پخش‌کننده
            // مثال: [{time: 12.34, text: "متن آهنگ"}, ...]
            'parsed_lyrics' => !empty($data['syncedLyrics'])
                ? $this->parseSyncedLyrics($data['syncedLyrics'])
                : null,
        ];
    }
}