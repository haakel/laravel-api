<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'cover_path',
    ];

    protected $casts = [
        'is_public' => 'boolean', // تضمین می‌کند که is_public به عنوان boolean ذخیره شود
    ];

    /**
     * رابطه چند-به-یک با کاربر (سازنده پلی‌لیست)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * رابطه چند-به-چند با آهنگ‌ها از طریق جدول پیوت
     */
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class)
                    ->withPivot('position') // شامل فیلد position از جدول پیوت شود
                    ->orderBy('position') // به طور پیش‌فرض بر اساس position مرتب شود
                    ->withTimestamps(); // شامل timestamps شود
    }
}