<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Song extends Model
{
    use HasFactory;

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
     * رابطه چند-به-یک با کاربر (آپلودکننده آهنگ)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function year(): BelongsTo
    {
        return $this->belongsTo(Year::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * رابطه چند-به-چند با پلی‌لیست‌ها از طریق جدول پیوت
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class)
                    ->withPivot('position') // شامل فیلد position از جدول پیوت شود
                    ->withTimestamps(); // شامل timestamps شود
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }



}