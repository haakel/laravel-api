<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * مدل کاربر
 *
 * نمایش اطلاعات کاربر در سیستم مدیریت موسیقی.
 * شامل احراز هویت با JWT، مدیریت آهنگ‌ها و لیست‌های پخش و علاقه‌مندی‌ها.
 * این مدل از Authenticatable ارث‌بری می‌کند و رابطه JWTSubject را پیاده‌سازی می‌کند.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * فیلدهای قابل پر کردن (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * فیلدهای مخفی (قابل نمایش نیستند در خروجی JSON)
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * تبدیل نوع فیلدها (type casting)
     *
     * تبدیل تاریخ تأیید ایمیل به datetime و رمز عبور به رشته هش‌شده.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * رابطه یک به چند با آهنگ‌ها
     *
     * هر کاربر می‌تواند آهنگ‌های متعددی آپلود کرده باشد.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    /**
     * رابطه یک به چند با لیست‌های پخش
     *
     * هر کاربر می‌تواند لیست‌های پخش متعددی ایجاد کند.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }

    /**
     * رابطه چند به چند با آهنگ‌های مورد علاقه
     *
     * آهنگ‌هایی که کاربر به لیست علاقه‌مندی‌ها اضافه کرده است.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favoriteSongs()
    {
        return $this->belongsToMany(Song::class, 'song_user')
            ->withTimestamps();
    }

    /**
     * دریافت شناسه اصلی کاربر برای توکن JWT
     *
     * این متد توسط کتابخانه JWTAuth فراخوانی می‌شود تا شناسه کاربر
     * را برای صدور توکن دریافت کند.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * دریافت ادعاهای سفارشی برای توکن JWT
     *
     * ادعاهای اضافی که به توکن JWT اضافه می‌شوند.
     * در این پیاده‌سازی هیچ ادعای سفارشی تعریف نشده است.
     *
     * @return array<int, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
