<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait AuthorizesUserSong
{
    /**
     * بررسی می‌کند که کاربر جاری مالک مدل باشد.
     *
     * @param  \App\Models\Song  $song
     * @return \App\Models\User
     */
    public function authorizeSongOwner($song)
    {
        $user = auth()->user(); // کاربر جاری از JWT یا Sanctum

        if (!$user) {
            abort(response()->json(['message' => 'Unauthenticated'], 401));
        }

        if ($song->user_id !== $user->id) {
            abort(response()->json(['message' => 'Unauthorized'], 403));
        }

        return $user;
    }
}