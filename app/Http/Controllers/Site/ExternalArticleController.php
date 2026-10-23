<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ExternalArticle;
use Illuminate\View\View;

class ExternalArticleController extends Controller
{
    public function show(ExternalArticle $externalArticle): View
    {
        return view('external.show', ['item' => $externalArticle->load('category')]);
    }
}