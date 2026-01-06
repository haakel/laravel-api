<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SongResource;
use App\Models\Song;
use App\Http\Requests\song\StoreSongRequest;


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

        $song = Song::create([
            'user_id' => $data->user_id,
            'title' => $data->title,
            'artist_id' => $data->artist_id,
            'album' => $data->album ?? null,
            'year_id' => $data->year_id ?? null,
            'genre_id' => $data->genre_id ?? null,
            'duration' => $data->duration ?? 0,
            'path' => $songPath,       // مسیر ذخیره شده
            'cover_path' => $coverPath, // مسیر ذخیره شده
            'plays' => 0,              // مقدار اولیه
        ]);

        return new SongResource($song);
    }
}