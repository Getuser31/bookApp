<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    /**
     * Filters the library books based on the provided authors and genres.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterLibrary(Request $request): JsonResponse
    {
        $userId = Auth()->id();
        $authorsId = explode(',', $request->input('authors'));
        $genresId = explode(',', $request->input('genres'));

        if ($authorsId[0] != '' && $genresId['0'] == '') {
            $books = Book::getListOfBooksFilterByAuthorId($authorsId, $userId);
        } elseif ($genresId[0] != '' && $authorsId['0'] == '') {
            $books = Book::getListOfBooksFilterByGenreId($genresId, $userId);
        } elseif ($authorsId['0'] == '' && $genresId['0'] == '') {
            $books = Auth()->user()->books()->with(['author', 'genres'])->get()->toArray();
        } else {
            $books = Book::getListOfBooksFilterByAuthorIdAndGenreId($authorsId, $genresId, $userId);
        }

        return response()->json(['books' => $books]);
    }

    public function updateRating(Request $request): JsonResponse
    {
        try {
            // Validate Request Input
            $validated = $request->validate([
                'bookId' => 'required|exists:books,id',
                'rating' => 'required|integer|min:1|max:10',
            ]);

            $bookId = $validated['bookId'];
            $ratingInput = $validated['rating'];

            // Attach User
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $userId = $user->id;

            // Get the existing BookRating record or create a new instance
            $bookRating = BookRating::firstOrNew(
                ['book_id' => $bookId, 'user_id' => $userId]
            );

            // Update the rating value
            $bookRating->rating = $ratingInput;

            // Save the BookRating record
            $bookRating->save();

            return response()->json(['message' => 'Rating updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFavorite(Request $request): JsonResponse
    {

        try {
            $favorite = $request->input('favorite');
            if ($favorite === 'false'){
                $favorite = false;
            } else {
                $favorite = true;
            }
            $bookId = $request->input('bookId');
            $book = Book::findOrFail($bookId);
            $user = Auth::user();
            $user->books()->updateExistingPivot($book, ['favorite' => $favorite]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);

    }
}
