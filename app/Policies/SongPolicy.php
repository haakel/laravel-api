<?php

namespace App\Policies;

use App\Models\Song;
use App\Models\User;

/**
 * سیاست دسترسی آهنگ‌ها — فقط مالک بتواند ویرایش/حذف کند
 */
class SongPolicy
{
    /**
     * آیا کاربر می‌تواند لیست آهنگ‌ها را ببیند؟ (همیشه true)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * آیا کاربر می‌تواند جزئیات آهنگ را ببیند؟ (همیشه true)
     */
    public function view(User $user, Song $song): bool
    {
        return $song->user_id === $user->id || $song->user_id !== null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * آیا کاربر می‌تواند آهنگ را ویرایش کند؟ (فقط مالک)
     */
    public function update(User $user, Song $song): bool
    {
        return $user->id === $song->user_id;
    }

    /**
     * آیا کاربر می‌تواند آهنگ را حذف کند؟ (فقط مالک)
     */
    public function delete(User $user, Song $song): bool
    {
        return $user->id === $song->user_id;
    }
}
