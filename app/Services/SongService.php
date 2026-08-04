<?php

namespace App\Services;

use App\Models\Song;
use App\Repositories\SongRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * سرویس مدیریت آهنگ‌ها
 *
 * این کلاس شامل عملیات اصلی CRUD آهنگ‌ها، استخراج متادیتای فایل‌های صوتی
 * با استفاده از کتابخانه getID3، و مدیریت شمارنده پخش آهنگ‌ها است.
 * تمام عملیات دسترسی به دیتابیس از طریق SongRepository انجام می‌شود.
 *
 * @package App\Services
 */
class SongService
{
    /**
     * سازنده کلاس — وابستگی SongRepository را از طریق تزریق وابستگی دریافت می‌کند
     *
     * @param SongRepository $repository مخزن داده آهنگ‌ها برای عملیات دسترسی به دیتابیس
     */
    public function __construct(protected SongRepository $repository) {}

    /**
     * دریافت لیست تمام آهنگ‌ها به صورت صفحه‌بندی شده
     *
     * @param array|null $filters آرایه‌ای از فیلترها برای محدود کردن نتایج (پیش‌فرض: آرایه خالی)
     *
     * @return LengthAwarePaginator شیء صفحه‌بندی حاوی لیست آهنگ‌ها (هر صفحه ۱۵ آیتم)
     */
    public function getAll(?array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate(15, $filters);
    }

    /**
     * دریافت یک آهنگ بر اساس شناسه (ID)
     *
     * @param int $id شناسه یکتای آهنگ در دیتابیس
     *
     * @return Song|null شیء مدل Song در صورت یافتن، در غیر این صورت null
     */
    public function getById(int $id): ?Song
    {
        return $this->repository->findById($id);
    }

    /**
     * ایجاد آهنگ جدید با آپلود فایل صوتی و تصویر جلد اختیاری
     *
     * این متد فایل صوتی و تصویر جلد را ذخیره کرده، متادیتای مدت زمان فایل صوتی را
     * استخراج می‌کند و سپس رکورد آهنگ را در دیتابیس ایجاد می‌کند.
     * نام فایل‌ها شامل اسلاگ عنوان، شناسه کاربر و زمان ایجاد برای یکتایی است.
     *
     * @param array $data آرایه داده‌های آهنگ شامل:
     *                    - 'title' (string): عنوان آهنگ (الزامی)
     *                    - 'artist_id' (int): شناسه هنرمند (الزامی)
     *                    - 'album' (string|null): عنوان آلبوم (اختیاری)
     *                    - 'year_id' (int|null): شناسه سال انتشار (اختیاری)
     *                    - 'genre_id' (int|null): شناسه ژانر (اختیاری)
     * @param UploadedFile $songFile فایل صوتی آپلود شده
     * @param UploadedFile|null $coverFile فایل تصویر جلد آلبوم (اختیاری)
     *
     * @return Song شیء مدل Song ایجاد شده در دیتابیس
     */
    public function create(array $data, UploadedFile $songFile, ?UploadedFile $coverFile = null): Song
    {
        $user = auth()->user();
        $time = time();
        $titleSlug = Str::slug($data['title']);

        $songFilename = "{$titleSlug}_{$user->id}_{$time}.{$songFile->getClientOriginalExtension()}";
        $songPath = $songFile->storeAs('songs', $songFilename, 'public');

        $coverPath = null;
        if ($coverFile) {
            $coverFilename = "{$titleSlug}_{$user->id}_{$time}.{$coverFile->getClientOriginalExtension()}";
            $coverPath = $coverFile->storeAs('covers', $coverFilename, 'public');
        }

        $fullPath = storage_path('app/public/' . $songPath);
        $duration = $this->getAudioDuration($fullPath);

        return $this->repository->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'artist_id' => $data['artist_id'],
            'album' => $data['album'] ?? null,
            'year_id' => $data['year_id'] ?? null,
            'genre_id' => $data['genre_id'] ?? null,
            'duration' => $duration,
            'path' => $songPath,
            'cover_path' => $coverPath,
            'plays' => 0,
        ]);
    }

    /**
     * به‌روزرسانی اطلاعات یک آهنگ موجود
     *
     * فقط فیلدهایی که در آرایه data ارسال شده‌اند به‌روزرسانی می‌شوند.
     * در صورت ارسال فایل تصویر جلد جدید، فایل قبلی با فایل جدید جایگزین می‌شود.
     *
     * @param array $data آرایه داده‌های به‌روزرسانی شامل فیلدهای اختیاری:
     *                    - 'title' (string|null): عنوان جدید آهنگ
     *                    - 'artist_id' (int|null): شناسه هنرمند جدید
     *                    - 'album' (string|null): عنوان آلبوم جدید
     *                    - 'year_id' (int|null): شناسه سال انتشار جدید
     *                    - 'genre_id' (int|null): شناسه ژانر جدید
     * @param Song $song شیء مدل آهنگی که باید به‌روزرسانی شود
     * @param UploadedFile|null $coverFile فایل تصویر جلد جدید (اختیاری)
     *
     * @return Song شیء مدل Song به‌روزرسانی شده
     */
    public function update(array $data, Song $song, ?UploadedFile $coverFile = null): Song
    {
        $updateData = [
            'title' => $data['title'] ?? $song->title,
            'artist_id' => $data['artist_id'] ?? $song->artist_id,
            'album' => $data['album'] ?? $song->album,
            'year_id' => $data['year_id'] ?? $song->year_id,
            'genre_id' => $data['genre_id'] ?? $song->genre_id,
        ];

        if ($coverFile) {
            $time = time();
            $titleSlug = Str::slug($updateData['title']);
            $coverFilename = "{$titleSlug}_{$song->user_id}_{$time}.{$coverFile->getClientOriginalExtension()}";
            $updateData['cover_path'] = $coverFile->storeAs('covers', $coverFilename, 'public');
        }

        return $this->repository->update($song, $updateData);
    }

    /**
     * حذف یک آهنگ از دیتابیس
     *
     * @param Song $song شیء مدل آهنگی که باید حذف شود
     *
     * @return bool true در صورت موفقیت حذف، false در صورت عدم موفقیت
     */
    public function delete(Song $song): bool
    {
        return $this->repository->delete($song);
    }

    /**
     * استخراج متادیتای فایل صوتی با استفاده از کتابخانه getID3
     *
     * این متد فایل صوتی آپلود شده را تحلیل کرده و اطلاعاتی مانند عنوان، هنرمند،
     * آلبوم، سال، ژانر، مدت زمان و بیت‌ریت را استخراج می‌کند.
     * ابتدا تگ‌های HTML و در صورت عدم وجود، تگ‌های ID3v2 بررسی می‌شوند.
     *
     * @param UploadedFile $file فایل صوتی آپلود شده برای استخراج متادیتا
     *
     * @return array آرایه‌ای شامل متادیتای استخراج شده از فایل صوتی:
     *              - 'title' (string): عنوان آهنگ
     *              - 'artist' (string): نام هنرمند
     *              - 'album' (string): عنوان آلبوم
     *              - 'year' (string): سال انتشار
     *              - 'genre' (string): ژانر موسیقی
     *              - 'duration' (float|int): مدت زمان بر حسب ثانیه
     *              - 'bitrate' (int): بیت‌ریت فایل صوتی
     */
    public function extractMetadata(UploadedFile $file): array
    {
        $getID3 = new \getID3();
        $fullPath = $file->getRealPath();
        $info = $getID3->analyze($fullPath);
        \getid3_lib::CopyTagsToComments($info);

        return [
            'title' => $info['comments_html']['title'][0] ?? $info['tags']['id3v2']['title'][0] ?? '',
            'artist' => $info['comments_html']['artist'][0] ?? $info['tags']['id3v2']['artist'][0] ?? '',
            'album' => $info['comments_html']['album'][0] ?? $info['tags']['id3v2']['album'][0] ?? '',
            'year' => $info['comments_html']['year'][0] ?? $info['tags']['id3v2']['year'][0] ?? '',
            'genre' => $info['comments_html']['genre'][0] ?? $info['tags']['id3v2']['genre'][0] ?? '',
            'duration' => $info['playtime_seconds'] ?? 0,
            'bitrate' => $info['bitrate'] ?? 0,
        ];
    }

    /**
     * دریافت مدت زمان فایل صوتی بر حسب ثانیه
     *
     * این متد فایل صوتی مسیر داده شده را با استفاده از getID3 تحلیل کرده
     * و مدت زمان پخش را به صورت عدد صحیح گرد شده برمی‌گرداند.
     *
     * @param string $fullPath مسیر کامل فایل صوتی در سیستم فایل
     *
     * @return int مدت زمان فایل صوتی بر حسب ثانیه (عدد صحیح)، در صورت عدم تشخیص ۰ برمی‌گرداند
     */
    protected function getAudioDuration(string $fullPath): int
    {
        $getID3 = new \getID3();
        $info = $getID3->analyze($fullPath);

        return isset($info['playtime_seconds'])
            ? (int) round($info['playtime_seconds'])
            : 0;
    }

    /**
     * جستجوی آهنگ‌ها بر اساس فیلترها با صفحه‌بندی
     *
     * @param array $filters آرایه‌ای از فیلترها برای محدود کردن نتایج جستجو
     * @param int $perPage تعداد آیتم‌ها در هر صفحه (پیش‌فرض: ۱۵)
     *
     * @return LengthAwarePaginator شیء صفحه‌بندی حاوی نتایج جستجو
     */
    public function search(array $filters, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filters);
    }

    /**
     * افزایش شمارنده تعداد پخش یک آهنگ
     *
     * هر بار فراخوانی این متد، شمارنده پخش آهنگ در دیتابیس به مقدار یک واحد افزایش می‌یابد.
     *
     * @param Song $song شیء مدل آهنگی که باید شمارنده پخش آن افزایش یابد
     *
     * @return void
     */
    public function incrementPlays(Song $song): void
    {
        $this->repository->incrementPlays($song);
    }
}
