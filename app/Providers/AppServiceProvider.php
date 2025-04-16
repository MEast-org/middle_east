<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\company;
use App\Models\User;
use App\Models\ads;
use App\Models\job_opportunity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         // معالجة أخطاء قاعدة البيانات

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'user' => User::class,
            'company' => company::class,
            'ads' =>ads::class,
            'job_opportunity' =>job_opportunity::class,
        ]);
    }
}
