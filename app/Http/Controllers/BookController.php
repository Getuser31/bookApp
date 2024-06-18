<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    /**
     * Retrieves a book with associated users and displays the book view with user progression information.
     *
     * @param int $id The ID of the book to retrieve.
     * @return Factory|\Illuminate\Foundation\Application|View|\App\Application The view containing the book and user progression information.
     */
    public function show(int $id): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $book = Book::with('users')->findOrFail($id);
        /** @var User $user */
        if (isset($book->users)) {
            $user = $book->users->first();
        }
        return view('book.book', ['book' => $book, 'progression' => $user->pivot->progression]);
    }

    /**
     * Updates the progression of a book for the authenticated user.
     *
     * @param Request $request The HTTP request containing the book ID and the new progression.
     * @return JsonResponse The JSON response indicating the success of the update.
     */
    public function updateProgression(Request $request): JsonResponse
    {
        $book = Book::findOrFail($request->input('bookId'));
        $user = Auth::user();
        $user->books()->updateExistingPivot($book, ['progression' => $request->input('progression')]);

        return response()->json(['success' => true]);
    }
}
