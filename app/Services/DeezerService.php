<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس جستجو و دریافت اطلاعات از Deezer API
 *
 * این سرویس برای جستجوی آهنگ، دریافت metadata کامل، کاور آرت، و لیست آلبوم‌ها
 * از پلتفرم Deezer استفاده می‌شود. نیازی به API Key ندارد.
 *
 * API Documentation: https://developers.deezer.com/api
 */
class DeezerService
{
    /**
     * آدرس پایه Deezer API
     */
    protected string $baseUrl = 'https://api.deezer.com';

    /**
     * ساخت کلاینت HTTP با تنظیمات مشترک
     *
     * - تایم‌اوت ۱۰ ثانیه‌ای برای جلوگیری از hangs طولانی
     * - در محیط لوکال (API_SSL_VERIFY=false) گواهی SSL غیرفعال می‌شود
     *   تا مشکل Certificate Verification پیش نیاد
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
     * جستجوی آهنگ در Deezer با عبارت آزاد
     *
     * مثال: search("Bohemian Rhapsody Queen")
     *
     * @param string $query   عبارت جستجو (مثلاً "artist + track")
     * @param int $limit      حداکثر تعداد نتایج (پیش‌فرض: ۵)
     * @return array          آرایه‌ای از آهنگ‌های پیدا شده (نرمال‌سازی شده)
     */
    public function search(string $query, int $limit = 5): array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/search", [
                    'q' => $query,
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                Log::error('Deezer search failed', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            $data = $response->json();

            if (empty($data['data'])) {
                return [];
            }

            // نرمال‌سازی نتایج خام Deezer به فرمت استاندارد پروژه
            return collect($data['data'])->map(function ($track) {
                return $this->normalizeTrack($track);
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Deezer connection error', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    /**
     * جستجوی آهنگ با عنوان و نام هنرمند
     *
     * این متد راحتی‌ترین روش جستجوست — فقط عنوان و هنرمند رو بده
     *
     * @param string $title       عنوان آهنگ
     * @param string|null $artist  نام هنرمند (اختیاری)
     * @return array              نتایج جستجو
     */
    public function searchByTitleAndArtist(string $title, ?string $artist = null): array
    {
        // ترکیب عنوان و هنرمند برای جستجوی دقیق‌تر
        $query = $artist
            ? "{$artist} {$title}"
            : $title;

        return $this->search($query, 5);
    }

    /**
     * جستجوی آهنگ با کد ISRC
     *
     * ISRC (International Standard Recording Code) کد یکتای هر آهنگ در سطح جهانی است.
     * با این کد می‌توان دقیق‌ترین نتیجه ممکن را پیدا کرد.
     *
     * مثال: searchByISRC("USWB10800123")
     *
     * @param string $isrc  کد ISRC آهنگ
     * @return array|null   اطلاعات آهنگ یا null در صورت پیدا نشدن
     */
    public function searchByISRC(string $isrc): ?array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/search", [
                    'q' => "isrc:{$isrc}",
                    'limit' => 1,
                ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            if (empty($data['data'][0])) {
                return null;
            }

            return $this->normalizeTrack($data['data'][0]);
        } catch (\Exception $e) {
            Log::error('Deezer ISRC search error', [
                'error' => $e->getMessage(),
                'isrc' => $isrc,
            ]);
            return null;
        }
    }

    /**
     * دریافت اطلاعات کامل یک آهنگ با ID آن در Deezer
     *
     * @param int $trackId  شناسه آهنگ در Deezer
     * @return array|null   اطلاعات کامل آهنگ یا null
     */
    public function getTrack(int $trackId): ?array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/track/{$trackId}");

            if ($response->failed()) {
                return null;
            }

            return $this->normalizeTrack($response->json());
        } catch (\Exception $e) {
            Log::error('Deezer track fetch error', [
                'error' => $e->getMessage(),
                'track_id' => $trackId,
            ]);
            return null;
        }
    }

    /**
     * دریافت اطلاعات کامل یک آلبوم شامل لیست آهنگ‌ها
     *
     * @param int $albumId  شناسه آلبوم در Deezer
     * @return array|null   اطلاعات آلبوم + لیست آهنگ‌ها یا null
     */
    public function getAlbum(int $albumId): ?array
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/album/{$albumId}");

            if ($response->failed()) {
                return null;
            }

            $album = $response->json();

            return [
                'id' => $album['id'],
                'title' => $album['title'],
                'artist' => $album['artist']['name'] ?? '',
                'cover_big' => $album['cover_big'] ?? '',
                'cover_xl' => $album['cover_xl'] ?? '',
                'release_date' => $album['release_date'] ?? '',
                'track_count' => $album['nb_tracks'] ?? 0,
                'label' => $album['label'] ?? '',
                // نرمال‌سازی لیست آهنگ‌های آلبوم
                'tracks' => collect($album['tracks']['data'] ?? [])->map(function ($track) {
                    return $this->normalizeTrack($track);
                })->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Deezer album fetch error', [
                'error' => $e->getMessage(),
                'album_id' => $albumId,
            ]);
            return null;
        }
    }

    /**
     * دریافت لینک پیش‌گوش ۳۰ ثانیه‌ای آهنگ
     *
     * Deezer برای هر آهنگ یک پیش‌گوش MP3 30 ثانیه‌ای رایگان دارد
     *
     * @param int $trackId  شناسه آهنگ در Deezer
     * @return string|null   URL فایل پیش‌گوش یا null
     */
    public function getPreview(int $trackId): ?string
    {
        try {
            $response = $this->http()
                ->get("{$this->baseUrl}/track/{$trackId}");

            if ($response->failed()) {
                return null;
            }

            return $response->json('preview');
        } catch (\Exception $e) {
            Log::error('Deezer preview fetch error', [
                'error' => $e->getMessage(),
                'track_id' => $trackId,
            ]);
            return null;
        }
    }

    /**
     * نرمال‌سازی اطلاعات خام آهنگ Deezer به فرمت استاندارد پروژه
     *
     * فرمت خام Deezer متفاوت از فرمت مورد نیاز ماست.
     * این متد فیلدها رو به ساختار یکسان تبدیل می‌کند.
     *
     * @param array $track  داده خام آهنگ از Deezer API
     * @return array        داده نرمال‌سازی شده
     */
    protected function normalizeTrack(array $track): array
    {
        return [
            'deezer_id' => $track['id'],
            'title' => $track['title'] ?? '',
            'artist' => $track['artist']['name'] ?? '',
            'artist_id' => $track['artist']['id'] ?? null,
            'album' => $track['album']['title'] ?? '',
            'album_id' => $track['album']['id'] ?? null,
            'duration' => $track['duration'] ?? 0,        // مدت زمان به ثانیه
            'isrc' => $track['isrc'] ?? null,              // کد بین‌المللی ضبط صدا
            'preview_url' => $track['preview'] ?? null,    // لینک پیش‌گوش ۳۰ ثانیه‌ای
            'rank' => $track['rank'] ?? 0,                 // محبوبیت آهنگ (۰ تا ۱,۰۰۰,۰۰۰)
            'cover_small' => $track['album']['cover_small'] ?? '',   // ۵۶x۵۶
            'cover_medium' => $track['album']['cover_medium'] ?? '', // ۲۵۰x۲۵۰
            'cover_big' => $track['album']['cover_big'] ?? '',       // ۵۰۰x۵۰۰
            'cover_xl' => $track['album']['cover_xl'] ?? '',         // ۱۰۰۰x۱۰۰۰
        ];
    }
}