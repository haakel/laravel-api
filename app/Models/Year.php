<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * مدل سال انتشار
 *
 * نمایش سال انتشار آهنگ‌ها در سیستم مدیریت موسیقی.
 * هر سال می‌تواند به آهنگ‌های متعددی مرتبط باشد که در همان سال منتشر شده‌اند.
 */
class Year extends Model
{
    use HasFactory;

    /**
     * فیلدهای قابل پر کردن (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = ['value'];

    /**
     * رابطه یک به چند با آهنگ‌ها
     *
     * هر سال می‌تواند شامل آهنگ‌های متعددی باشد که در آن سال منتشر شده‌اند.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }
}
