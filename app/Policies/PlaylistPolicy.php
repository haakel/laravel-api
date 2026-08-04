<?php

namespace App\Policies;

use App\Models\Playlist;
use App\Models\User;

/**
 * سیاست دسترسی پلی‌لیست‌ها — فقط مالک بتواند ویرایش/حذف کند
 */
class PlaylistPolicy
{
    /**
     * آیا کاربر می‌تواند پلی‌لیست‌ها را ببیند؟ (همیشه true)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Playlist $playlist): bool
    {
        return $playlist->is_public || $playlist->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * آیا کاربر می‌تواند پلی‌لیست را ویرایش کند؟ (فقط مالک)
     */
    public function update(User $user, Playlist $playlist): bool
    {
        return $user->id === $playlist->user_id;
    }

    /**
     * آیا کاربر می‌تواند پلی‌لیست را حذف کند؟ (فقط مالک)
     */
    public function delete(User $user, Playlist $playlist): bool
    {
        return $user->id === $playlist->user_id;
    }

    public function manageSongs(User $user, Playlist $playlist): bool
    {
        return $user->id === $playlist->user_id;
    }
}
