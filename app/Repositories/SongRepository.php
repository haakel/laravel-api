<?php

namespace App\Repositories;

use App\Models\Song;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * مخزن داده آهنگ‌ها (Repository Pattern)
 *
 * این کلاس لایه دسترسی به داده‌ها را برای مدل آهنگ فراهم می‌کند.
 * از الگوی مخزن (Repository Pattern) استفاده می‌کند تا منطق دسترسی به داده‌ها
 * از کنترلرها جدا باشد و قابلیت آزمون‌پذیری و نگهداری افزایش یابد.
 * شامل عملیات جستجو، صفحه‌بندی، ایجاد، ویرایش و حذف آهنگ‌ها است.
 */
class SongRepository
{
    /**
     * سازنده کلاس مخزن آهنگ
     *
     * نمونه مدل Song از طریق تزریق وابستگی (Dependency Injection) دریافت می‌شود.
     *
     * @param \App\Models\Song $model نمونه مدل آهنگ
     */
    public function __construct(protected Song $model) {}

    /**
     * دریافت لیست صفحه‌بندی شده آهنگ‌ها با فیلترهای اختیاری
     *
     * آهنگ‌ها همراه با روابط خواننده، ژانر و سال دریافت می‌شوند.
     * فیلترها شامل جستجو بر اساس عنوان، شناسه خواننده، آلبوم، ژانر و سال هستند.
     * نتایج بر اساس جدیدترین مرتب می‌شوند.
     *
     * @param int $perPage تعداد آهنگ در هر صفحه (پیش‌فرض: ۱۵)
     * @param array<int, mixed> $filters آرایه فیلترها برای محدود کردن نتایج
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator صفحه نتایج
     */
    public function paginate(int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['artist', 'genre', 'year']);

        if (!empty($filters['title'])) {
            $query->where('title', 'like', "%{$filters['title']}%");
        }

        if (!empty($filters['artist_id'])) {
            $query->where('artist_id', $filters['artist_id']);
        }

        if (!empty($filters['album'])) {
            $query->where('album', 'like', "%{$filters['album']}%");
        }

        if (!empty($filters['genre_id'])) {
            $query->where('genre_id', $filters['genre_id']);
        }

        if (!empty($filters['year_id'])) {
            $query->where('year_id', $filters['year_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * جستجوی آهنگ بر اساس شناسه
     *
     * آهنگ را با تمام روابط مرتبط (خواننده، ژانر، سال و کاربر) برمی‌گرداند.
     *
     * @param int $id شناسه آهنگ
     *
     * @return \App\Models\Song|null نمونه آهنگ یا null در صورت عدم یافتن
     */
    public function findById(int $id): ?Song
    {
        return $this->model->with(['artist', 'genre', 'year', 'user'])->find($id);
    }

    /**
     * ایجاد آهنگ جدید
     *
     * یک آهنگ جدید در پایگاه داده ایجاد می‌کند.
     *
     * @param array<string, mixed> $data آرایه اطلاعات آهنگ
     *
     * @return \App\Models\Song نمونه آهنگ ایجاد شده
     */
    public function create(array $data): Song
    {
        return $this->model->create($data);
    }

    /**
     * به‌روزرسانی اطلاعات آهنگ
     *
     * اطلاعات آهنگ موجود را با داده‌های جدید به‌روزرسانی می‌کند.
     *
     * @param \App\Models\Song $song نمونه آهنگی که باید به‌روزرسانی شود
     * @param array<string, mixed> $data آرایه اطلاعات جدید
     *
     * @return \App\Models\Song نمونه آهنگ به‌روزرسانی شده
     */
    public function update(Song $song, array $data): Song
    {
        $song->update($data);
        return $song->fresh();
    }

    /**
     * حذف آهنگ (حذف نرم)
     *
     * آهنگ را از پایگاه داده حذف نرم (soft delete) می‌کند.
     * رکورد به‌طور فیزیکی حذف نمی‌شود و قابل بازیابی است.
     *
     * @param \App\Models\Song $song نمونه آهنگی که باید حذف شود
     *
     * @return bool نتیجه عملیات حذف
     */
    public function delete(Song $song): bool
    {
        return $song->delete();
    }

    /**
     * افزایش شمارنده پخش آهنگ
     *
     * تعداد دفعات پخش آهنگ را به میزان یک واحد افزایش می‌دهد.
     *
     * @param \App\Models\Song $song نمونه آهنگی که پخش شده است
     *
     * @return void
     */
    public function incrementPlays(Song $song): void
    {
        $song->increment('plays');
    }

    /**
     * دریافت آهنگ‌های یک کاربر خاص
     *
     * تمام آهنگ‌هایی که توسط کاربر مشخصی آپلود شده‌اند را برمی‌گرداند.
     *
     * @param int $userId شناسه کاربر
     *
     * @return \Illuminate\Database\Eloquent\Collection مجموعه آهنگ‌های کاربر
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }
}
