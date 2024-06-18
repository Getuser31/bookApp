<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $book = Book::with('users')->findOrFail($id);
        /** @var User $user */
        $user = $book->users->first();
        return view('book.book', ['book' => $book, 'progression' => $user->pivot->progression]);
    }

    public function updateProgression(Request $request): \Illuminate\Http\JsonResponse
    {
        $book = Book::findOrFail($request->input('bookId'));
        $user = Auth::user();
        $user->books()->updateExistingPivot($book, ['progression' => $request->input('progression')]);

        return response()->json(['success' => true]);
    }
}
