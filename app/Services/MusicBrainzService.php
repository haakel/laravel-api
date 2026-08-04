<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس جستجوی MusicBrainz
 *
 * این کلاس برای جستجوی اطلاعات متادیتای ضبط‌های موسیقی از وب‌سرویس MusicBrainz استفاده می‌شود.
 * از طریق این سرویس می‌توان عنوان، نام هنرمند، آلبوم، سال انتشار و تصویر جلد آلبوم را دریافت کرد.
 *
 * @package App\Services
 */
class MusicBrainzService
{
    /**
     * آدرس پایه وب‌سرویس MusicBrainz برای جستجوی ضبط‌ها
     *
     * @var string
     */
    protected string $baseUrl = 'https://musicbrainz.org/ws/2/recording/';

    /**
     * رشته شناسایی کاربر (User-Agent) که در درخواست‌های HTTP ارسال می‌شود.
     * طبق قوانین MusicBrainz، ارسال User-Agent الزامی است.
     *
     * @var string
     */
    protected string $userAgent = 'MusicApp/1.0 (contact@musicapp.com)';

    /**
     * جستجوی ضبط‌های موسیقی بر اساس عبارت جستجو
     *
     * این متد یک درخواست HTTP به وب‌سرویس MusicBrainz ارسال کرده و نتایج جستجو را
     * به صورت آرایه‌ای از اطلاعات ساده‌شده ضبط‌ها برمی‌گرداند. در صورت بروز خطا،
     * خطا ثبت شده و آرایه خالی برگردانده می‌شود.
     *
     * @param string $query عبارت جستجو (عنوان آهنگ، نام هنرمند، یا ترکیبی از هر دو)
     * @param int $limit حداکثر تعداد نتایج برگشتی (پیش‌فرض: ۵)
     *
     * @return array آرایه‌ای از آرایه‌ها شامل اطلاعات هر ضبط:
     *              - 'title' (string): عنوان آهنگ
     *              - 'artist' (string): نام هنرمند اصلی
     *              - 'album' (string): عنوان آلبوم
     *              - 'year' (string): سال انتشار (۴ رقم)
     *              - 'cover' (string|null): آدرس تصویر جلد آلبوم یا null
     */
    public function search(string $query, int $limit = 5): array
    {
        try {
            $response = Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'User-Agent' => $this->userAgent,
            ])
            ->timeout(10)
            ->get($this->baseUrl, [
                'query' => $query,
                'fmt' => 'json',
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                Log::error('MusicBrainz API failed', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return [];
            }

            $data = $response->json();

            if (empty($data['recordings'])) {
                return [];
            }

            return collect($data['recordings'])->map(function ($rec) {
                return [
                    'title' => $rec['title'] ?? '',
                    'artist' => $rec['artist-credit'][0]['name'] ?? '',
                    'album' => $rec['releases'][0]['title'] ?? '',
                    'year' => substr($rec['releases'][0]['date'] ?? '', 0, 4),
                    'cover' => !empty($rec['releases'][0]['id'])
                        ? "https://coverartarchive.org/release/{$rec['releases'][0]['id']}/front"
                        : null,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('MusicBrainz connection error', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return [];
        }
    }

    /**
     * جستجوی ضبط موسیقی بر اساس عنوان و نام هنرمند
     *
     * این متد عنوان آهنگ و نام هنرمند را ترکیب کرده و فقط اولین نتیجه جستجو را برمی‌گرداند.
     * مناسب برای حالتی است که اطلاعات دقیق‌تری از آهنگ در دسترس است.
     *
     * @param string $title عنوان آهنگ
     * @param string|null $artist نام هنرمند (اختیاری — اگر null باشد فقط عنوان جستجو می‌شود)
     *
     * @return array آرایه‌ای شامل حداکثر یک نتیجه با ساختار مشابه متد search()
     *              - 'title' (string): عنوان آهنگ
     *              - 'artist' (string): نام هنرمند اصلی
     *              - 'album' (string): عنوان آلبوم
     *              - 'year' (string): سال انتشار
     *              - 'cover' (string|null): آدرس تصویر جلد آلبوم
     */
    public function searchByTitleAndArtist(string $title, ?string $artist = null): array
    {
        $query = trim(implode(' ', array_filter([$title, $artist])));
        return $this->search($query, 1);
    }
}
