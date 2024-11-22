<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AddNoteRequest;
use App\Http\Requests\StoreBookPost;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\Genre;
use App\Models\Notes;
use App\Models\User;
use App\Services\GoogleBookService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
        $books = Auth()->user()->books()->with(['author', 'genres'])->get();
        return response()->json([
            'books' => $books,
            'authors' => $authors,
            'genres' => $genres
        ]);
    }

    public function bookShow(int $id): JsonResponse
    {
        $book = Book::with('users', 'author', 'genres')->findOrFail($id);
        $rating = BookRating::getRating($book->id, auth()->id());
        $notes = Notes::getNotesForBookAndUser(auth()->id(), $book->id);
        if ($rating !== null) {
            $rating = $rating->rating;
        }
        $progression = null;
        $favorite = null;
        $belongToUser = null;
        /** @var User $user */
        if (isset($book->users)) {
            $user = $book->users->first();
            if ($user != null) {
                $progression = $user->pivot->progression;
                $favorite = $user->pivot->favorite;
                $belongToUser = true;
            }
        }

        return response()->json([
            'book' => $book,
            'progression' => $progression,
            'belongToUser' => $belongToUser,
            'rating' => $rating,
            'favorite' => $favorite,
            'notes' => $notes
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function googleBookStore(string $id): JsonResponse
    {
        $googleBookService = new GoogleBookService($id);
        $book = $googleBookService->storeBook($id);

        return response()->json(['success' => true, 'message' => 'book added to library', 'id' => $book->id]);
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

    public function updateProgression(Request $request): JsonResponse
    {
        try {
            // Validate Request Input
            $validated = $request->validate([
                'bookId' => 'required|exists:books,id',
                'progression' => 'required|integer|min:1|max:100',
            ]);
            $progression = $validated['progression'];

            // Attach User
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $book = Book::findOrFail($request->input('bookId'));
            $user->books()->updateExistingPivot($book, ['progression' => $progression]);

            if ($progression == 100){
                $date = new \DateTime('now');
                $user->books()->updateExistingPivot($book, ['completed_at' => $date]);
            }

            return response()->json(['message' => 'Progression updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeNote(addNoteRequest $request): JsonResponse
    {
        // Creating the note using mass assignment
        $note = Notes::create([
            'content' => $request->input('content'),
            'user_id' => Auth::id(),
            'book_id' => $request->input('bookId'),
        ]);

        return response()->json([
            'note' => $note,
            'success' => true
        ]);

    }

    /**
     * @return JsonResponse
     */
    public function library(): JsonResponse
    {
        $genres = Genre::all()->sortBy('name');
        $authors = Book::getListOfAuthorsBasedOnUserLibrary(auth()->id());
        $books = Auth()->user()->books()->with(['author', 'genres', 'ratings'])->get();
        return response()->json([
            'books' => $books,
            'genres' => $genres,
            'authors' => $authors
        ]);
    }

    /**
     * Return list of All Genres
     * @return JsonResponse
     */
    public function handleGenre(): JsonResponse
    {
        $genres = Genre::all();
        return response()->json([
            'genres' => $genres
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function handleAuthors(): JsonResponse
    {
        $authors = Author::all();
        return response()->json([
            'authors' => $authors
        ]);
    }

    /**
     * Store a new author.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeAuthor(Request $request): JsonResponse
    {
        $author = new Author();
        $validatedData = $request->validate(['author' => 'required|max:255']);
        $author->name = $validatedData['author'];
        try {
            $author->save();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => 'author added to library', 'id' => $author->id]);

    }

    /**
     * Store a new book.
     *
     * @param StoreBookPost $request The incoming request object.
     * @return JsonResponse The redirect response after storing the book.
     */
    public function storeBook(StoreBookPost $request): JsonResponse
    {
        $validatedData = $request->validated();
        $book = new Book();

        try {
            $book->storeFromRequest($validatedData);
        } catch (Exception $e){
            return response()->json(['error' => $e->getMessage()]);
        }
        return response()->json(['success' => true, 'message' => 'book added to library', 'id' => $book->id]);
    }

    /**
     * @param StoreBookPost $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateBook(StoreBookPost $request, int $id): JsonResponse
    {
        $book = Book::findOrFail($id);
        $user = Auth::user();
        $validatedData = $request->validated();

        if ($book->user_id === $user->id){ // It's already a book that belong to the user, so just update it
            $validatedData['date_of_publication'] = $book->getFormattedDate($validatedData['date_of_publication']);
            $book->update($validatedData);
            $bookId = $book->id;
        } else { // This is a google Book API, we need to create new one to let the original available
            $newBook = new Book();
            $newBook->storeFromRequest($validatedData);
            $newBook->user_id = $user->id;
            $newBook->save();
            $user->books()->detach($book);

            $this->migrateBookAttributes($validatedData, $book, $newBook, $user);
            $bookId = $newBook->id;
        }

        return response()->json(['success' => true, 'message' => 'book updated', 'id' => $bookId]);
    }

    /**
     * @param mixed $validatedData
     * @param Model|Collection|array|Book $book
     * @param Book $newBook
     * @param User|Authenticatable|null $user
     * @return void
     */
    private function migrateBookAttributes(mixed $validatedData, Model|Collection|array|Book $book, Book $newBook, User|Authenticatable|null $user): void
    {
        if (!isset($validatedData['picture'])) { // We need to transfer original picture @todo upload a copy to avoid problem when delete original picture
            $newBook->picture = $book->picture;
            $newBook->save();
        }

        // If the book has already been rated, we have to transfer it to the new book
        $rating = new BookRating();
        $isRated = $rating->getRating($book->id, $user->id);
        if ($isRated) {
            $isRated->book_id = $newBook->id;
            $isRated->save();
        }

        //If the book belong some notes, we have to transfer it to the new book
        $notes = new Notes();
        $doesBookGetNotes = $notes->getNotesForBookAndUser($user->id, $book->id);
        if ($doesBookGetNotes) {
            foreach ($doesBookGetNotes as $note) {
                $note->book_id = $newBook->id;
                $note->save();
            }

        }
    }
}
