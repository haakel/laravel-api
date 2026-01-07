<?php

namespace App\Http\Controllers\api;

use getID3;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\SongResource;
use App\Http\Resources\DeleteSongResource;
use App\Http\Requests\song\EditSongRequest;
use App\Http\Requests\song\StoreSongRequest;
use App\Http\Requests\song\DeleteSongRequest;
use App\Http\Requests\song\GetDataSongRequest;


class SongController extends Controller
{
    function index() {
        return  SongResource::collection(Song::paginate(10));
    }

    public function store(StoreSongRequest $request)
    {
        $data = $request->validated();
        $songPath = $request->file('song_file')->store('songs', 'public');
        $coverPath = $request->hasFile('cover_file')? $request->file('cover_file')->store('covers', 'public'): null;
        $fullPath = storage_path('app/public/' . $songPath);
        $duration = $this->getAudioDuration($fullPath);
        $song = Song::create([
            'user_id'    => $data['user_id'],
            'title'      => $data['title'],
            'artist_id'  => $data['artist_id'],
            'album'      => $data['album'] ?? null,
            'year_id'    => $data['year_id'] ?? null,
            'genre_id'   => $data['genre_id'] ?? null,
            'duration'   => $duration,
            'path' => $songPath,       // مسیر ذخیره شده
            'cover_path' => $coverPath, // مسیر ذخیره شده
            'plays' => 0,              // مقدار اولیه
        ]);

        return new SongResource($song);
    }

    private function getAudioDuration(string $fullPath): int
    {
        $getID3 = new getID3();
        $info = $getID3->analyze($fullPath);

        return isset($info['playtime_seconds'])
            ? (int) round($info['playtime_seconds'])
            : 0;
    }

    // public function GetDataSong(GetDataSongRequest $request)
    // {
    //     $getID3 = new getID3();
    //     $songFile = $request->file('song_file');
    //     $fullPath = $songFile->getRealPath();

    //     $info = $getID3->analyze($fullPath);
    //     Log::info('getID3 raw tags', ['tags' => json_encode($info['tags'])]);
    //     \getid3_lib::CopyTagsToComments($info);

    //     $getID3Data = [
    //         'title'    => $info['comments_html']['title'][0] ?? $info['tags']['id3v2']['title'][0] ?? '',
    //         'artist'   => $info['comments_html']['artist'][0] ?? $info['tags']['id3v2']['artist'][0] ?? '',
    //         'album'    => $info['comments_html']['album'][0] ?? $info['tags']['id3v2']['album'][0] ?? '',
    //         'year'     => $info['comments_html']['year'][0] ?? $info['tags']['id3v2']['year'][0] ?? '',
    //         'genre'    => $info['comments_html']['genre'][0] ?? $info['tags']['id3v2']['genre'][0] ?? '',
    //         'duration' => $info['playtime_seconds'] ?? 0,
    //         'bitrate'  => $info['bitrate'] ?? 0,
    //     ];

    //     return response()->json($getID3Data);
    // }


    public function GetDataSong(GetDataSongRequest $request)
    {
        $getID3 = new getID3();
        $songFile = $request->file('song_file');
        $fullPath = $songFile->getRealPath();

        $info = $getID3->analyze($fullPath);
        \getid3_lib::CopyTagsToComments($info);

        // ---------- ✅ اطلاعات اولیه از فایل ----------
        $getID3Data = [
            'title'    => $info['comments_html']['title'][0] ?? $info['tags']['id3v2']['title'][0] ?? "",
            'artist'   => $info['comments_html']['artist'][0] ?? $info['tags']['id3v2']['artist'][0] ?? "",
            'album'    => $info['comments_html']['album'][0] ?? $info['tags']['id3v2']['album'][0] ?? "",
            'year'     => $info['comments_html']['year'][0] ?? $info['tags']['id3v2']['year'][0] ?? "",
            'genre'    => $info['comments_html']['genre'][0] ?? $info['tags']['id3v2']['genre'][0] ?? "",
            'duration' => $info['playtime_seconds'] ?? 0,
            'bitrate'  => $info['bitrate'] ?? 0,
        ];

        Log::info('getID3 raw tags', ['tags' => json_encode($info['tags'])]);

        // ---------- ✅ جستجو در MusicBrainz ----------
        $musicBrainzData = [];

        if (!empty($getID3Data['title']) || !empty($getID3Data['artist'])) {
            $mbQuery = urlencode($getID3Data['title'] . ' ' . $getID3Data['artist']);
            $mbUrl = "https://musicbrainz.org/ws/2/recording/?query=$mbQuery&fmt=json&limit=1";

            $ch = curl_init($mbUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'MusicApp/1.0 (contact@yourapp.com)',
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5
            ]);

            $mbResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($mbResponse && $httpCode === 200) {
                $mbData = json_decode($mbResponse, true);

                if (!empty($mbData['recordings'][0])) {
                    $rec = $mbData['recordings'][0];
                    $musicBrainzData = [
                        'title'  => $rec['title'] ?? '',
                        'artist' => $rec['artist-credit'][0]['name'] ?? '',
                        'album'  => $rec['releases'][0]['title'] ?? '',
                        'year'   => substr($rec['releases'][0]['date'] ?? '', 0, 4),
                        'cover'  => !empty($rec['releases'][0]['id'])
                                    ? "https://coverartarchive.org/release/{$rec['releases'][0]['id']}/front"
                                    : null,
                    ];
                }
            }
        }

        // ---------- ✅ خروجی نهایی با دو بخش جدا ----------
        $finalData = [
            'getID3' => $getID3Data,
            'musicBrainz' => $musicBrainzData
        ];

        return response()->json($finalData, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroysong(DeleteSongRequest $request)
    {
        // return response()->json(['message' => 'Song deleted successfully.'], 200);
        $song = Song::find($request->song_id);
        if (!$song) {
            return response()->json(['message' => 'Song not found.'], 404);
        }

        $song->delete();
        return new DeleteSongResource($song);
    }

    public function editsong(EditSongRequest $request)
    {
        // return response()->json(['message' => 'Song edited successfully.'], 200);
        $song = Song::find($request->song_id);
        if (!$song) {
            return response()->json(['message' => 'Song not found.'], 404);
        }
        $coverPath = $request->hasFile('cover_file')? $request->file('cover_file')->store('covers', 'public'): null;

        log::info('EditSongRequest validated data', ['data' => $request->validated()]);
        $song->update([
            'title'      => $request->title,
            'artist_id'  => $request->artist_id,
            'album'      => $request->album ?? null,
            'year_id'    => $request->year_id ?? null,
            'genre_id'   => $request->genre_id ?? null,
            'cover_path' => $coverPath ?? $song->cover_path,
        ]);

        return new SongResource($song);
    }

}