<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Song;

class FavoriteSongController extends Controller
{
    // گرفتن آهنگ‌های مورد علاقه کاربر
    public function index(Request $request)
    {
        $user = $request->user(); // با JWT یا auth
        return response()->json($user->favoriteSongs, 200);
    }

    // اضافه کردن آهنگ به علاقه‌مندی
    public function add(Request $request)
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $user = $request->user();
        $user->favoriteSongs()->syncWithoutDetaching($request->song_id);

        return response()->json(['message' => 'Song added to favorites'], 200);
    }

    // حذف آهنگ از علاقه‌مندی
    public function remove(Request $request)
    {
        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $user = $request->user();
        $user->favoriteSongs()->detach($request->song_id);

        return response()->json(['message' => 'Song removed from favorites'], 200);
    }
}