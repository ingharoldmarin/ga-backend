<?php

namespace App\Providers;

use App\Models\CurriculumGrid;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        User::observe(AuditObserver::class);
        CurriculumGrid::observe(AuditObserver::class);
    }
}
