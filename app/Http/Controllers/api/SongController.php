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
        $song = Song::create($request->validated());

        return new SongResource($song);
    }
}