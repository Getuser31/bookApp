<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function index(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('index');
    }
}
