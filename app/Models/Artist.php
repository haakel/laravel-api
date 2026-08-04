<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * مدل هنرمند (خواننده)
 *
 * نمایش اطلاعات یک هنرمند یا خواننده در سیستم مدیریت موسیقی.
 * هر هنرمند دارای یک نام منحصربفرد است و می‌تواند آهنگ‌های متعددی داشته باشد.
 */
class Artist extends Model
{
    use HasFactory;

    /**
     * فیلدهای قابل پر کردن (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];
}
