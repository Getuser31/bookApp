<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $genres = Genre::all();
        $authors = Book::getListOfAuthorsBasedOnUserLibrary(auth()->id());
        $books = Auth()->user()->books()->with(['author', 'genres'])->paginate(10);
        return response()->json([
           'books' => $books,
           'authors' => $authors,
           'genres' => $genres
       ]);
    }

    public function filterLibrary(Request $request): JsonResponse
    {
        $userId = Auth()->id();
        $authorsId = $request->input('authors');
        $genresId = $request->input('genres');

        $booksFilterByAuthors = Book::getListOfBooksFilterByAuthorId($authorsId, $userId);
        $booksFilterByGenres = Book::getListOfBooksFilterByGenreId($genresId, $userId);

        return response()->json([]);
    }
}
