<?php

namespace App\Services;

use App\Repositories\FavoriteRepository;
use Illuminate\Database\Eloquent\Collection;

class FavoriteService
{
    /**
     * سرویس مدیریت آهنگ‌های مورد علاقه کاربر
     *
     * @param FavoriteRepository $repository
     */
    public function __construct(protected FavoriteRepository $repository) {}

    public function getAll(int $userId): Collection
    {
        return $this->repository->getByUserId($userId);
    }

    /**
     * افزودن آهنگ به علاقه‌مندی‌ها
     *
     * @param int $userId  شناسه کاربر
     * @param int $songId  شناسه آهنگ
     * @return bool        true در صورت موفقیت
     */
    public function add(int $userId, int $songId): void
    {
        $this->repository->add($userId, $songId);
    }

    /**
     * حذف آهنگ از علاقه‌مندی‌ها
     *
     * @param int $userId  شناسه کاربر
     * @param int $songId  شناسه آهنگ
     * @return bool        true در صورت موفقیت
     */
    public function remove(int $userId, int $songId): void
    {
        $this->repository->remove($userId, $songId);
    }
}
