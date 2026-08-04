<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * مدل ژانر (سبک موسیقی)
 *
 * نمایش یک ژانر یا سبک موسیقی در سیستم مدیریت موسیقی.
 * مانند: پاپ، راک، جاز، کلاسیک، هیپ‌هاپ و غیره.
 * هر ژانر دارای یک نام منحصربفرد است و می‌تواند به آهنگ‌های متعددی مرتبط باشد.
 */
class Genre extends Model
{
    use HasFactory;

    /**
     * فیلدهای قابل پر کردن (mass assignable)
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];
}
