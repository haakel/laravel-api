<?php

namespace App\Providers;

use App\Models\Playlist;
use App\Models\Song;
use App\Policies\PlaylistPolicy;
use App\Policies\SongPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Song::class => SongPolicy::class,
        Playlist::class => PlaylistPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
