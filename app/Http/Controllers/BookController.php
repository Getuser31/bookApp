<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function index(): Factory|\Illuminate\Foundation\Application|View|Application|RedirectResponse
    {
        if(!Auth::check()){
            return redirect()->route('login');
        }

        $books = Auth()->user()->books()->with('author')->with('genre')->paginate(10);


        return view('books',  ['books' => $books]);
    }

    public function library(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('user.library', ['books' => Auth()->user()->books()->with('author')->with('genre')->paginate(10)]);
    }

    public function show(int $id): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $book = Book::findOrFail($id);
        return view('book.book', ['book' => $book]);
    }
}
