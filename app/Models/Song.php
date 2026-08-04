<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * مدل آهنگ
 *
 * نمایش یک آهنگ در سیستم مدیریت موسیقی.
 * شامل اطلاعاتی مانند عنوان، خواننده، آلبوم، ژانر، سال، مدت زمان و تعداد پخش است.
 * از SoftDeletes برای حذف نرم و از HasFactory برای ساخت فیکچر استفاده می‌کند.
 */
class Song extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * فیلدهای قابل پر کردن (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'artist_id',
        'album',
        'year_id',
        'genre_id',
        'duration',
        'path',
        'cover_path',
        'plays',
    ];

    /**
     * رابطه با مدل کاربر (آپلودکننده آهنگ)
     *
     * هر آهنگ توسط یک کاربر آپلود شده است.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * رابطه با مدل ژانر
     *
     * هر آهنگ متعلق به یک ژانر (سبک موسیقی) است.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    /**
     * رابطه با مدل سال
     *
     * هر آهنگ دارای یک سال انتشار است.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    /**
     * رابطه با مدل خواننده (هنرمند)
     *
     * هر آهنگ توسط یک خواننده اجرا شده است.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * رابطه چند به چند با لیست‌های پخش
     *
     * یک آهنگ می‌تواند در چندین لیست پخش وجود داشته باشد.
     * جدول واسط position برای ترتیب قرارگیری آهنگ در لیست پخش را نگه می‌دارد.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class)
            ->withPivot('position')
            ->withTimestamps();
    }

    /**
     * رابطه چند به چند با کاربرانی که آهنگ را به علاقه‌مندی‌ها اضافه کرده‌اند
     *
     * آهنگ‌ها توسط کاربران مختلف می‌توانند به لیست علاقه‌مندی‌ها اضافه شوند.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'song_user')
            ->withTimestamps();
    }
}
