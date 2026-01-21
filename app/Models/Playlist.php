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


    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class)
                    ->withPivot('position')
                    ->withTimestamps();
    }

    /**
     * Playlist ↔ Songs
     */
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(
                Song::class,
                'playlist_song',   // اسم جدول pivot
                'playlist_id',
                'song_id'
            )
            ->withPivot('position')
            ->orderBy('position')
            ->withTimestamps();
    }
}