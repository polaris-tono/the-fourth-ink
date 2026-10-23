<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Contributor\ArticleController as ContributorArticleController;
use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\CategoryController;
use App\Http\Controllers\Site\ExternalArticleController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\SearchController;
use App\Http\Controllers\Site\WritersBlockController;
use Illuminate\Support\Facades\Route;

// ---- Public site (no account needed) ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/article/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/news/{externalArticle}', [ExternalArticleController::class, 'show'])->name('external.show');
Route::get('/writers-block', [WritersBlockController::class, 'index'])->name('writers-block');

// ---- Authentication (guests only, rate limited) ----
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ---- Contributor area (Writers' Block desk) ----
Route::middleware(['auth', 'role:contributor,admin'])
    ->prefix('my/articles')
    ->name('contributor.')
    ->where(['article' => '[0-9]+'])
    ->group(function () {
        Route::get('/', [ContributorArticleController::class, 'index'])->name('dashboard');
        Route::get('/create', [ContributorArticleController::class, 'create'])->name('articles.create');
        Route::post('/', [ContributorArticleController::class, 'store'])->name('articles.store');
        Route::get('/{article}', [ContributorArticleController::class, 'show'])->name('articles.show');
        Route::get('/{article}/edit', [ContributorArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/{article}', [ContributorArticleController::class, 'update'])->name('articles.update');
        Route::post('/{article}/submit', [ContributorArticleController::class, 'submit'])->name('articles.submit');
        Route::delete('/{article}', [ContributorArticleController::class, 'destroy'])->name('articles.destroy');
    });

// ---- Admin / editorial desk ----
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [ReviewController::class, 'dashboard'])->name('dashboard');

        // Review queue
        Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
        Route::get('/review/{article}', [ReviewController::class, 'show'])->name('review.show')->whereNumber('article');
        Route::post('/review/{article}/transition', [ReviewController::class, 'transition'])
            ->name('review.transition')->whereNumber('article');

        // Article management
        Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [AdminArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit')->whereNumber('article');
        Route::put('/articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update')->whereNumber('article');
        Route::delete('/articles/{article}', [AdminArticleController::class, 'destroy'])->name('articles.destroy')->whereNumber('article');
        Route::post('/articles/{article}/restore', [AdminArticleController::class, 'restore'])
            ->name('articles.restore')->whereNumber('article')->withTrashed();
        Route::post('/articles/{article}/feature', [AdminArticleController::class, 'feature'])
            ->name('articles.feature')->whereNumber('article');

        // User management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role')->whereNumber('user');
        Route::patch('/users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('users.active')->whereNumber('user');

        // Category management
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    });