<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * منبع JSON آهنگ (Resource)
 *
 * این کلاس تبدیل داده‌های مدل آهنگ به ساختار JSON استاندارد را انجام می‌دهد.
 * از قابلیت‌های Resource لاراول استفاده می‌کند تا اطلاعات آهنگ شامل خواننده،
 * ژانر، سال و آدرس کاور را به شکل یکپارچه و ساختاریافته برگرداند.
 * از whenLoaded برای بارگذاری شرطی روابط استفاده می‌شود.
 */
class SongResource extends JsonResource
{
    /**
     * تبدیل مدل آهنگ به آرایه JSON
     *
     * ساختار خروجی شامل شناسه، عنوان، خواننده (بصورت تو در تو با ArtistResource)，
     * ژانر، سال، آلبوم، مدت زمان، آدرس کاور، تعداد پخش و زمان‌ها است.
     * اگر کاوری وجود نداشته باشد، تصویر پیش‌فرض نمایش داده می‌شود.
     *
     * @param \Illuminate\Http\Request $request درخواست HTTP
     *
     * @return array<string, mixed> آرایه ساختاریافته JSON آهنگ
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'artist' => new ArtistResource($this->whenLoaded('artist')),
            'genre' => new GenreResource($this->whenLoaded('genre')),
            'year' => $this->whenLoaded('year', fn() => $this->year?->value),
            'album' => $this->album,
            'duration' => $this->duration,
            'cover_url' => $this->cover_path
                ? asset("storage/{$this->cover_path}")
                : asset('images/default-cover.jpg'),
            'plays' => $this->plays,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
