<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    /**
     * Retrieves the list of books associated with the authenticated user and displays the books view.
     *
     * @return Factory|\Illuminate\Foundation\Application|View|\App\Application|RedirectResponse The view containing the list of books.
     */
    public function index(): Factory|\Illuminate\Foundation\Application|View|Application|RedirectResponse
    {
        if(!Auth::check()){
            return redirect()->route('login');
        }

        /** @var LengthAwarePaginator $books */
        $books = Auth()->user()->books()->with('author')->with('genre')->paginate(10);


        return view('books',  ['books' => $books]);
    }

    /**
     * Retrieves the library of the authenticated user and displays it in the library view.
     *
     * @return Factory|\Illuminate\Foundation\Application|View|\App\Application The view containing the user's library.
     */
    public function library(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $genres = Genre::all();
        return view('user.library', ['books' => Auth()->user()->books()->with('author')->with('genre')->paginate(10),   'genres' => $genres]);
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
        $progression = null;
        $belongToUser = null;
        /** @var User $user */
        if (isset($book->users)) {
            $user = $book->users->first();
            if($user != null){
                $progression = $user->pivot->progression;
                $belongToUser = true;
            }
        }
        return view('book.book', ['book' => $book, 'progression' => $progression, 'belongToUser' => $belongToUser]);
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

    public function addBook(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        return view('book.addBook');
    }

    public function addBookPost(int $id): RedirectResponse
    {
        $book = Book::findOrFail($id);
        $user = Auth::user();

        $user->books()->attach($book);

        return redirect()->route('book.library');
    }

    public function searchBook(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $books = Book::where('title', 'like', '%' . $search . '%')
            ->orWhere('author', 'like', '%' . $search . '%')
            ->orWhere('genre', 'like', '%' . $search . '%')
            ->get();

        return response()->json(['books' => $books]);
    }

    /**
     * Deletes a book from the database.
     *
     * @param int $id The ID of the book to delete.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    public function deleteBook(int $id): JsonResponse
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json(['success' => true]);
    }

    public function removeBook(int $id): JsonResponse
    {
        $user = Auth::user();
        $user->books()->detach($id);

        return response()->json(['success' => true]);
    }
}
