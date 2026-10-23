<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The navigation bar is built from the categories table, so admins can manage it.
        View::composer('layouts.app', function ($view) {
            $view->with('navCategories', Category::orderBy('sort_order')->get());
        });
    }
}