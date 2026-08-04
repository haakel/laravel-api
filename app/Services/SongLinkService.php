<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس لینک‌سازی بین‌پلتفرمی با Song.link (odesli.co)
 *
 * با استفاده از این سرویس می‌توان یک آهنگ را از هر پلتفرمی (Spotify, YouTube, Deezer, ...)
 * شناسایی کرد و لینک آن روی تمام پلتفرم‌های دیگر را دریافت کرد.
 *
 * API Documentation: https://github.com/aderathon/songlink-api
 * وب‌سایت: https://odesli.co
 */
class SongLinkService
{
    /**
     * آدرس پایه Song.link API
     */
    protected string $baseUrl = 'https://api.song.link/v1-alpha.1';

    /**
     * کد کشور پیش‌فرض برای نتایج منطقه‌ای
     * برخی آهنگ‌ها در کشورهای مختلف متفاوت هستند
     */
    protected string $userCountry = 'US';

    /**
     * ساخت کلاینت HTTP با تنظیمات مشترک
     *
     * - تایم‌اوت ۱۵ ثانیه‌ای (سرور Song.link گاهی کند پاسخ می‌دهد)
     * - در محیط لوکال (API_SSL_VERIFY=false) گواهی SSL غیرفعال می‌شود
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(15);

        // غیرفعال کردن SSL verify در محیط توسعه لوکال
        // روی سرور تولید این شرط هیچوقت اجرا نمی‌شود
        if (!config('services_api.ssl_verify')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * دریافت لینک‌های بین‌پلتفرمی با استفاده از URL
     *
     * یک لینک از هر پلتفرمی بده (Spotify, YouTube, Deezer, Tidal, ...)
     * و لینک همون آهنگ روی تمام پلتفرم‌های دیگر رو بگیر.
     *
     * مثال:
     *   getLinksByURL("https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC")
     *   → لینک Spotify + Deezer + YouTube + Tidal + ... برای همون آهنگ
     *
     * @param string $url         لینک آهنگ از هر پلتفرمی
     * @param string|null $country  کد کشور (اختیاری، پیش‌فرض: US)
     * @return array|null          اطلاعات + لینک‌های تمام پلتفرم‌ها
     */
    public function getLinksByURL(string $url, ?string $country = null): ?array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/links", [
                    'url' => $url,
                    'userCountry' => $country ?? $this->userCountry,
                ]);

            if ($response->failed()) {
                Log::error('SongLink API failed', [
                    'status' => $response->status(),
                    'url' => $url,
                ]);
                return null;
            }

            $data = $response->json();

            return $this->normalizeResponse($data);
        } catch (\Exception $e) {
            Log::error('SongLink connection error', [
                'error' => $e->getMessage(),
                'url' => $url,
            ]);
            return null;
        }
    }

    /**
     * دریافت لینک‌های بین‌پلتفرمی با استفاده از ISRC
     *
     * این دقیق‌ترین روش است — با کد ISRC آهنگ، نتیجه ۱۰۰٪ دقیق خواهد بود
     *
     * @param string $isrc        کد ISRC آهنگ (مثلاً "USWB10800123")
     * @param string|null $country  کد کشور (اختیاری)
     * @return array|null          اطلاعات + لینک‌های تمام پلتفرم‌ها
     */
    public function getLinksByISRC(string $isrc, ?string $country = null): ?array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/links", [
                    'isrc' => $isrc,
                    'userCountry' => $country ?? $this->userCountry,
                ]);

            if ($response->failed()) {
                Log::error('SongLink ISRC search failed', [
                    'status' => $response->status(),
                    'isrc' => $isrc,
                ]);
                return null;
            }

            $data = $response->json();

            return $this->normalizeResponse($data);
        } catch (\Exception $e) {
            Log::error('SongLink ISRC connection error', [
                'error' => $e->getMessage(),
                'isrc' => $isrc,
            ]);
            return null;
        }
    }

    /**
     * جستجوی آهنگ با عنوان و هنرمند در Song.link
     *
     * این متد از API جستجوی Song.link استفاده می‌کند
     * و اولین نتیجه (بهترین تطابق) را برمی‌گرداند.
     *
     * @param string $title   عنوان آهنگ
     * @param string $artist   نام هنرمند
     * @return array|null      اطلاعات اولین نتیجه یا null
     */
    public function searchByTitleAndArtist(string $title, string $artist): ?array
    {
        try {
            $query = "{$title} {$artist}";
            $response = $this->http()
                ->get("{$this->baseUrl}/search", [
                    'q' => $query,
                    'userCountry' => $this->userCountry,
                ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            // اگر نتیجه‌ای برنگشت
            if (empty($data['sections'][0]['items'])) {
                return null;
            }

            // اولین نتیجه = بهترین تطابق با عبارت جستجو
            $firstResult = $data['sections'][0]['items'][0];

            return [
                'title' => $firstResult['title'] ?? '',
                'artist' => $firstResult['artist'] ?? '',
                'thumbnail' => $firstResult['thumbnail'] ?? '',
                'duration' => $firstResult['duration'] ?? 0,
                'url' => $firstResult['url'] ?? '',
                'provider' => $firstResult['provider'] ?? '', // پلتفرم منبع (spotify, youtube, ...)
            ];
        } catch (\Exception $e) {
            Log::error('SongLink search error', [
                'error' => $e->getMessage(),
                'title' => $title,
                'artist' => $artist,
            ]);
            return null;
        }
    }

    /**
     * استخراج Spotify Track ID از لینک Spotify
     *
     * پشتیبانی از فرمت‌های مختلف:
     *   - https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC
     *   - spotify:track:4uLU6hMCjMI75M1A2tKUQC
     *
     * @param string $url  لینک Spotify
     * @return string|null  شناسه آهنگ یا null اگر لینک معتبر نباشد
     */
    public function extractSpotifyID(string $url): ?string
    {
        $patterns = [
            '/open\.spotify\.com\/track\/([a-zA-Z0-9]+)/',
            '/spotify:track:([a-zA-Z0-9]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * استخراج YouTube Video ID از لینک YouTube
     *
     * پشتیبانی از فرمت‌های مختلف:
     *   - https://www.youtube.com/watch?v=dQw4w9WgXcQ
     *   - https://youtu.be/dQw4w9WgXcQ
     *   - https://www.youtube.com/embed/dQw4w9WgXcQ
     *
     * @param string $url  لینک YouTube
     * @return string|null  شناسه ویدیو یا null اگر لینک معتبر نباشد
     */
    public function extractYouTubeID(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * نرمال‌سازی پاسخ خام Song.link API به فرمت استاندارد پروژه
     *
     * ساختار خام API شامل دو بخش اصلی است:
     *   - linksByPlatform: لینک‌های هر پلتفرم
     *   - entitiesByUniqueId: اطلاعات آهنگ (عنوان، هنرمند، تصویر، ...)
     *
     * @param array $data  پاسخ خام از Song.link API
     * @return array       داده نرمال‌سازی شده
     */
    protected function normalizeResponse(array $data): array
    {
        $links = $data['linksByPlatform'] ?? [];
        $entities = $data['entitiesByUniqueId'] ?? [];

        // پیدا کردن اطلاعات اصلی آهنگ از بین entities
        // معمولاً اولین entity که title و artistName دارد، آهنگ اصلی است
        $trackInfo = null;
        foreach ($entities as $entityId => $entity) {
            if (isset($entity['title']) && isset($entity['artistName'])) {
                $trackInfo = $entity;
                break;
            }
        }

        return [
            // اطلاعات اصلی آهنگ
            'title' => $trackInfo['title'] ?? null,
            'artist' => $trackInfo['artistName'] ?? null,
            'thumbnail' => $trackInfo['thumbnailUrl'] ?? null,
            'thumbnail_width' => $trackInfo['thumbnailWidth'] ?? null,
            'duration' => $trackInfo['duration'] ?? null,

            // لینک‌های تمام پلتفرم‌ها
            // null یعنی آهنگ روی اون پلتفرم موجود نیست
            'platforms' => [
                'spotify' => $links['spotify']['url'] ?? null,
                'deezer' => $links['deezer']['url'] ?? null,
                'youtube' => $links['youtube']['url'] ?? null,
                'tidal' => $links['tidal']['url'] ?? null,
                'amazon_music' => $links['amazonMusic']['url'] ?? null,
                'soundcloud' => $links['soundcloud']['url'] ?? null,
                'apple_music' => $links['appleMusic']['url'] ?? null,
            ],

            // شناسه یکتای آهنگ در سیستم Song.link
            'entity_unique_id' => $data['entityUniqueId'] ?? null,
        ];
    }
}