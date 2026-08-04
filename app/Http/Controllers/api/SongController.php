<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\song\StoreSongRequest;
use App\Http\Requests\song\EditSongRequest;
use App\Http\Requests\song\GetDataSongRequest;
use App\Http\Resources\SongResource;
use App\Http\Traits\ApiResponse;
use App\Models\Song;
use App\Services\SongService;
use App\Services\MusicBrainzService;
use App\Services\DeezerService;
use App\Services\SongLinkService;
use App\Services\LrcLibService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * کنترلر مدیریت آهنگ‌ها
 *
 * این کنترلر مسئول مدیریت عملیات CRUD روی آهنگ‌ها، جستجو، پخش استریم،
 * و دریافت متادیتا از سرویس‌های خارجی (MusicBrainz, Deezer, Song.link, LrcLib) است.
 *
 * وابستگی‌ها:
 * - SongService: سرویس اصلی مدیریت آهنگ‌ها
 * - MusicBrainzService: جستجوی اطلاعات آهنگ در MusicBrainz
 * - DeezerService: جستجو و دریافت اطلاعات از Deezer
 * - SongLinkService: دریافت لینک‌های cross-platform از Song.link
 * - LrcLibService: دریافت و جستجوی لیریکس آهنگ‌ها
 */
class SongController extends Controller
{
    use ApiResponse;

    /**
     * سازنده کنترلر - تزریق وابستگی‌ها از طریق Constructor Injection
     *
     * @param SongService $songService سرویس مدیریت آهنگ‌ها برای عملیات CRUD و جستجو
     * @param MusicBrainzService $musicBrainzService سرویس جستجوی اطلاعات آهنگ در پایگاه داده MusicBrainz
     * @param DeezerService $deezerService سرویس جستجو و دریافت اطلاعات از پلتفرم Deezer
     * @param SongLinkService $songLinkService سرویس دریافت لینک‌های پخش از پلتفرم‌های مختلف
     * @param LrcLibService $lrcLibService سرویس دریافت و جستجوی لیریکس آهنگ‌ها
     *
     * @return void
     */
    public function __construct(
        protected SongService $songService,
        protected MusicBrainzService $musicBrainzService,
        protected DeezerService $deezerService,
        protected SongLinkService $songLinkService,
        protected LrcLibService $lrcLibService
    ) {}

    /**
     * دریافت لیست تمام آهنگ‌ها با قابلیت فیلتر و صفحه‌بندی
     *
     * آهنگ‌ها بر اساس پارامترهای ارسالی (عنوان، هنرمند، آلبوم، ژانر، سال) فیلتر شده
     * و نتایج به صورت صفحه‌بندی شده با منابع SongResource برمی‌گردند.
     *
     * @param Request $request درخواست HTTP شامل پارامترهای فیلتر: title, artist_id, album, genre_id, year_id
     *
     * @return JsonResponse پاسخ JSON شامل لیست صفحه‌بندی شده آهنگ‌ها با منبع SongResource
     */
    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'title' => $request->title,
            'artist_id' => $request->artist_id,
            'album' => $request->album,
            'genre_id' => $request->genre_id,
            'year_id' => $request->year_id,
        ]);

        $songs = $this->songService->getAll($filters);

        return $this->paginatedResponse(SongResource::collection($songs));
    }

    /**
     * ایجاد آهنگ جدید
     *
     * آهنگ جدید را با استفاده از داده‌های تأیید شده، فایل صوتی و کاور ایجاد می‌کند.
     * فایل‌های آپلود شده شامل فایل صوتی آهنگ و تصویر کاور هستند.
     *
     * @param StoreSongRequest $request درخواست تأیید شده شامل اطلاعات آهنگ، فایل صوتی و کاور
     *
     * @return JsonResponse پاسخ JSON شامل آهنگ ایجاد شده با منابع artist, genre, year و کد وضعیت 201
     */
    public function store(StoreSongRequest $request): JsonResponse
    {
        $song = $this->songService->create(
            $request->validated(),
            $request->file('song_file'),
            $request->file('cover_file')
        );

        return $this->successResponse(
            new SongResource($song->load(['artist', 'genre', 'year'])),
            'Song created successfully',
            201
        );
    }

    /**
     * نمایش جزئیات یک آهنگ خاص
     *
     * آهنگ را بر اساس شناسه پیدا کرده و پس از بررسی مجوز دسترسی، اطلاعات آن را برمی‌گرداند.
     *
     * @param int $id شناسه آهنگ مورد نظر
     *
     * @return JsonResponse پاسخ JSON شامل اطلاعات آهنگ با منبع SongResource یا پیام خخطای 404
     */
    public function show(int $id): JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $this->authorize('view', $song);

        return $this->successResponse(new SongResource($song));
    }

    /**
     * ویرایش اطلاعات یک آهنگ موجود
     *
     * آهنگ را بر اساس شناسه پیدا کرده، مجوز ویرایش را بررسی کرده و سپس اطلاعات آن
     * شامل فایل کاور اختیاری را به‌روزرسانی می‌کند.
     *
     * @param EditSongRequest $request درخواست تأیید شده شامل اطلاعات جدید آهنگ و فایل کاور اختیاری
     * @param int $id شناسه آهنگی که باید به‌روزرسانی شود
     *
     * @return JsonResponse پاسخ JSON شامل آهنگ به‌روز شده با منابع artist, genre, year یا پیام خطای 404
     */
    public function update(EditSongRequest $request, int $id): JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $this->authorize('update', $song);

        $updated = $this->songService->update(
            $request->validated(),
            $song,
            $request->file('cover_file')
        );

        return $this->successResponse(
            new SongResource($updated->load(['artist', 'genre', 'year'])),
            'Song updated successfully'
        );
    }

    /**
     * حذف یک آهنگ
     *
     * آهنگ را بر اساس شناسه پیدا کرده، مجوز حذف را بررسی کرده و سپس آن را از پایگاه داده حذف می‌کند.
     *
     * @param int $id شناسه آهنگی که باید حذف شود
     *
     * @return JsonResponse پاسخ JSON با پیام موفقیت‌آمیز حذف یا پیام خطای 404
     */
    public function destroy(int $id): JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $this->authorize('delete', $song);

        $this->songService->delete($song);

        return $this->successResponse(null, 'Song deleted successfully');
    }

    /**
     * استخراج و دریافت متادیتای یک فایل صوتی
     *
     * متادیتای فایل صوتی ارسالی را استخراج کرده و سپس اطلاعات تکمیلی را از
     * MusicBrainz و Deezer بر اساس عنوان و نام هنرمند جستجو می‌کند.
     *
     * @param GetDataSongRequest $request درخواست تأیید شده شامل فایل صوتی برای استخراج متادیتا
     *
     * @return JsonResponse پاسخ JSON شامل سه بخش: metadata (متادیتای فایل)، music_brainz (اطلاعات MusicBrainz)، deezer (اطلاعات Deezer)
     */
    public function getMetadata(GetDataSongRequest $request): JsonResponse
    {
        $metadata = $this->songService->extractMetadata($request->file('song_file'));

        // MusicBrainz
        $musicBrainzData = [];
        if (!empty($metadata['title']) || !empty($metadata['artist'])) {
            $results = $this->musicBrainzService->searchByTitleAndArtist(
                $metadata['title'],
                $metadata['artist']
            );
            $musicBrainzData = $results[0] ?? [];
        }

        // Deezer
        $deezerData = [];
        if (!empty($metadata['title']) || !empty($metadata['artist'])) {
            $deezerResults = $this->deezerService->searchByTitleAndArtist(
                $metadata['title'],
                $metadata['artist']
            );
            $deezerData = $deezerResults[0] ?? [];
        }

        return $this->successResponse([
            'metadata' => $metadata,
            'music_brainz' => $musicBrainzData,
            'deezer' => $deezerData,
        ]);
    }

    /**
     * جستجو در Deezer
     * GET /songs/search-deezer?q=artist+track
     */
    public function searchDeezer(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string',
        ]);

        $results = $this->deezerService->search($request->q);

        if (empty($results)) {
            return $this->errorResponse('No tracks found on Deezer', 404);
        }

        return $this->successResponse([
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * جستجوی آهنگ در Deezer با ISRC
     * GET /songs/search-deezer-isrc?isrc=XX0000000000
     */
    public function searchDeezerByISRC(Request $request): JsonResponse
    {
        $request->validate([
            'isrc' => 'required|string',
        ]);

        $result = $this->deezerService->searchByISRC($request->isrc);

        if (!$result) {
            return $this->errorResponse('No track found with this ISRC', 404);
        }

        return $this->successResponse($result);
    }

    /**
     * دریافت لینک‌های cross-platform با Song.link
     * POST /songs/songlink
     * Body: { "url": "https://open.spotify.com/track/..." }
     */
    public function getSongLink(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $result = $this->songLinkService->getLinksByURL($request->url);

        if (!$result) {
            return $this->errorResponse('Could not find cross-platform links', 404);
        }

        return $this->successResponse($result);
    }

    /**
     * دریافت لینک‌های cross-platform با ISRC
     * GET /songs/songlink-isrc?isrc=XX0000000000
     */
    public function getSongLinkByISRC(Request $request): JsonResponse
    {
        $request->validate([
            'isrc' => 'required|string',
        ]);

        $result = $this->songLinkService->getLinksByISRC($request->isrc);

        if (!$result) {
            return $this->errorResponse('Could not find cross-platform links for this ISRC', 404);
        }

        return $this->successResponse($result);
    }

    /**
     * دریافت لیریکس آهنگ
     * GET /songs/lyrics?track=...&artist=...&duration=...&album=...
     */
    public function getLyrics(Request $request): JsonResponse
    {
        $request->validate([
            'track' => 'required|string',
            'artist' => 'required|string',
            'duration' => 'required|integer|min:1',
            'album' => 'nullable|string',
        ]);

        $result = $this->lrcLibService->getLyrics(
            $request->track,
            $request->artist,
            $request->duration,
            $request->album
        );

        if (!$result) {
            return $this->errorResponse('Lyrics not found', 404);
        }

        return $this->successResponse($result);
    }

    /**
     * جستجوی لیریکس
     * GET /songs/lyrics/search?q=...
     */
    public function searchLyrics(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string',
        ]);

        $results = $this->lrcLibService->search($request->q);

        if (empty($results)) {
            return $this->errorResponse('No lyrics found', 404);
        }

        return $this->successResponse([
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * دریافت اطلاعات آلبوم از Deezer
     * GET /songs/deezer-album/{albumId}
     */
    public function getDeezerAlbum(int $albumId): JsonResponse
    {
        $result = $this->deezerService->getAlbum($albumId);

        if (!$result) {
            return $this->errorResponse('Album not found on Deezer', 404);
        }

        return $this->successResponse($result);
    }

    /**
     * جستجوی آهنگ در پایگاه داده MusicBrainz
     *
     * آهنگ را بر اساس نام (عنوان) و نام هنرمند در پایگاه داده MusicBrainz جستجو کرده
     * و لیست نتایج را برمی‌گرداند.
     *
     * @param Request $request درخواست HTTP شامل پارامترهای: name (اجباری) و artist (اختیاری)
     *
     * @return JsonResponse پاسخ JSON شامل تعداد نتایج و لیست ضبط‌های یافت شده یا پیام خطای 404
     */
    public function searchMusicBrainz(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'artist' => 'nullable|string',
        ]);

        $results = $this->musicBrainzService->searchByTitleAndArtist(
            $request->name,
            $request->artist
        );

        if (empty($results)) {
            return $this->errorResponse('No recordings found', 404);
        }

        return $this->successResponse([
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * جستجوی پیشرفته آهنگ‌ها در پایگاه داده محلی
     *
     * آهنگ‌ها را بر اساس یک یا چند فیلتر (عنوان، شناسه هنرمند، آلبوم، شناسه ژانر، شناسه سال) جستجو کرده
     * و نتایج به صورت صفحه‌بندی شده با منابع SongResource برمی‌گرداند.
     *
     * @param Request $request درخواست HTTP شامل پارامترهای اختیاری: title, artist_id, album, genre_id, year_id
     *
     * @return JsonResponse پاسخ JSON شامل لیست صفحه‌بندی شده نتایج جستجو با منبع SongResource
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'nullable|string',
            'artist_id' => 'nullable|exists:artists,id',
            'album' => 'nullable|string',
            'genre_id' => 'nullable|exists:genres,id',
            'year_id' => 'nullable|exists:years,id',
        ]);

        $filters = array_filter([
            'title' => $request->title,
            'artist_id' => $request->artist_id,
            'album' => $request->album,
            'genre_id' => $request->genre_id,
            'year_id' => $request->year_id,
        ]);

        $songs = $this->songService->search($filters);

        return $this->paginatedResponse(SongResource::collection($songs));
    }

    /**
     * پخش استریم یک آهنگ
     *
     * فایل صوتی آهنگ را بر اساس شناسه پیدا کرده و به صورت استریم (پخش آنلاین) برمی‌گرداند.
     * تعداد پخش آهنگ نیز به صورت خودکار افزایش می‌یابد. هدرها شامل نوع MIME،
     * اندازه فایل، پشتیبانی از بازه‌ها و کش مرورگر تنظیم شده‌اند.
     *
     * @param int $id شناسه آهنگی که باید پخش شود
     *
     * @return StreamedResponse|JsonResponse پاسخ StreamedResponse برای پخش فایل صوتی یا JsonResponse با پیام خطای 404
     */
    public function stream(int $id): StreamedResponse|JsonResponse
    {
        $song = $this->songService->getById($id);

        if (!$song) {
            return $this->errorResponse('Song not found', 404);
        }

        $filePath = storage_path('app/public/' . $song->path);

        if (!file_exists($filePath)) {
            return $this->errorResponse('Audio file not found', 404);
        }

        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'audio/mpeg';

        // Increment play count
        $this->songService->incrementPlays($song);

        return response()->stream(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
            'Content-Disposition' => 'inline; filename="' . basename($song->path) . '"',
        ]);
    }
}
