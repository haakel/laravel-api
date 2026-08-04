<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * مدل لیست پخش (Playlist)
 *
 * نمایش یک لیست پخش در سیستم مدیریت موسیقی.
 * هر لیست پخش توسط یک کاربر ایجاد شده و شامل آهنگ‌های متعددی با ترتیب مشخص است.
 * قابلیت عمومی یا خصوصی بودن و حذف نرم را دارد.
 */
class Playlist extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * فیلدهای قابل پر کردن (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'cover_path',
    ];

    /**
     * تبدیل نوع فیلدها (type casting)
     *
     * تبدیل فیلد is_public به نوع بولین (true/false).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * رابطه با مدل کاربر
     *
     * هر لیست پخش توسط یک کاربر ایجاد شده است.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * رابطه چند به چند با آهنگ‌ها
     *
     * آهنگ‌ها در لیست پخش با ترتیب مشخص (position) قرار می‌گیرند.
     * جدول واسط playlist_song استفاده می‌شود و بر اساس position مرتب‌سازی می‌شود.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'playlist_song', 'playlist_id', 'song_id')
            ->withPivot('position')
            ->orderBy('position')
            ->withTimestamps();
    }
}
