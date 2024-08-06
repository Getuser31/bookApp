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
        $authorsId = explode(',',$request->input('authors'));
        $genresId = explode(',' ,$request->input('genres'));

        if ($authorsId[0] != '' && $genresId['0'] == '') {
            $books = Book::getListOfBooksFilterByAuthorId($authorsId, $userId);
        }elseif ($genresId[0] != '' && $authorsId['0'] == '') {
            $books = Book::getListOfBooksFilterByGenreId($genresId, $userId);
        }elseif ($authorsId['0'] == '' && $genresId['0'] == '') {
            $books = Auth()->user()->books()->with(['author', 'genres'])->get()->toArray();
        }
        else {
            $books = Book::getListOfBooksFilterByAuthorIdAndGenreId($authorsId, $genresId, $userId);
        }

        return response()->json(['books' => $books]);
    }
}
