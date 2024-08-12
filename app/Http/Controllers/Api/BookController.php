<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\Genre;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function updateRating(Request $request): JsonResponse
    {
        //Validate Request Input
        $validator = Validator::make($request->all(), [
            'bookId' => 'required|exists:books,id',
            'rating' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        $bookId = $request->input('bookId');
        $ratingInput = $request->input('rating');

        // Find the book by ID or fail
        Book::findOrFail($bookId);

        //Attach User
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $ratingId = BookRating::getRating($bookId, $userId);
        $rating = null;
        if($ratingId != null) {
            $rating = Rating::find($ratingId)->first();
        }

        if(!$rating) {
            $rating = Rating::create([
                'rating' => $ratingInput,
            ]);
        } else {
            $rating->update([
                'rating' => $ratingInput,
            ]);
        }

        // Update or create rating in the book_rating table
        BookRating::updateOrCreate(
            ['book_id' => $bookId, 'user_id' => $userId, 'rating_id' => $rating->id],
        );

        return response()->json(['message' => 'Rating updated successfully']);
    }
}
