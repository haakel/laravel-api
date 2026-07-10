<?php

namespace App\Services;

use App\Repositories\FavoriteRepository;
use Illuminate\Database\Eloquent\Collection;

class FavoriteService
{
    public function __construct(protected FavoriteRepository $repository) {}

    public function getAll(int $userId): Collection
    {
        return $this->repository->getByUserId($userId);
    }

    public function add(int $userId, int $songId): void
    {
        $this->repository->add($userId, $songId);
    }

    public function remove(int $userId, int $songId): void
    {
        $this->repository->remove($userId, $songId);
    }
}
