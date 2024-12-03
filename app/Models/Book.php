<?php

namespace App\Models;

use DateTime;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 *
 *
 * @property int $id
 * @property string $title
 * @property string $date_of_publication
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $author_id
 * @property int $genre_id
 * @property int|null $collection_id
 * @property-read Collection|null $collection
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static Builder|Book newModelQuery()
 * @method static Builder|Book newQuery()
 * @method static Builder|Book query()
 * @method static Builder|Book whereAuthorId($value)
 * @method static Builder|Book whereCollectionId($value)
 * @method static Builder|Book whereCreatedAt($value)
 * @method static Builder|Book whereDateOfPublication($value)
 * @method static Builder|Book whereDescription($value)
 * @method static Builder|Book whereGenreId($value)
 * @method static Builder|Book whereId($value)
 * @method static Builder|Book whereTitle($value)
 * @method static Builder|Book whereUpdatedAt($value)
 * @property-read Author|null $author
 * @property-read Genre|null $genre
 * @property string|null $picture
 * @method static Builder|Book wherePicture($value)
 * @property string|null $google_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Genre> $genres
 * @property-read int|null $genres_count
 * @method static Builder|Book whereGoogleId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Rating> $rating
 * @property-read int|null $rating_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BookRating> $ratings
 * @property-read int|null $ratings_count
 * @mixin Eloquent
 */
class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date_of_publication',
        'description',
        'author_id',
        'genre_id',
        'collection_id',
        'picture',
        'google_id',
        'user_id'
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Collection|array $books
     * @return array
     */
    public static function RetrieveProgression(\Illuminate\Database\Eloquent\Collection|array $books): array
    {
        // Transform books collection to include progression on the main object
        $booksWithProgression = $books->map(function ($book) {
            if ($book->users->isNotEmpty()) {
                // Assuming there will be only one user since we filtered by user_id
                $book->progression = $book->users->first()->pivot->progression;
                $book->unsetRelation('users');
            }
            return $book;
        });
        return $booksWithProgression->toArray();
    }

    /**
     * @param int $userId
     * @return \Closure
     */
    public static function getUserProgression(int $userId): \Closure
    {
        return function ($book) use ($userId) {
            // Access the user's progression from the pivot
            $user = $book->users->firstWhere('id', $userId);

            if ($user) {
                // Move progression to book->pivot->progression
                $book->pivot = (object)[
                    'progression' => data_get($user->pivot, 'progression')
                ];
            } else {
                $book->pivot = null;
            }

            $book->unsetRelation('users');

            return $book;
        };
    }

    public function storeFromRequest(array $validatedData): void
    {
        $formattedDate = $this->getFormattedDate($validatedData['date_of_publication']);

        $this->title = $validatedData['title'];
        $this->description = $validatedData['description'];
        $this->date_of_publication = $formattedDate;
        $this->collection_id = $validatedData['collection_id'] ?? null;
        $this->author_id = $validatedData['author_id'];
        $this->google_id = $validatedData['google_id'] ?? null;
        if (isset($validatedData['picture'])) {
            $this->FormatUploadedFile($validatedData);
        }


        $this->save();
        // Sync genres
        if (isset($validatedData['genres'])) {
            $this->genres()->sync($validatedData['genres']);
        }
        //Sync User
        $user = Auth::user();
        $user->books()->attach($this);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['progression', 'favorite', 'completed_at']);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(BookRating::class); // Relationship to book ratings
    }

    public static function getListOfAuthorsBasedOnUserLibrary(int $userId)
    {
        return self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('author')->get()->pluck('author')->unique('id')->values();
    }

    /**
     * @param array $validatedData
     * @return array
     */
    public function FormatUploadedFile(array $validatedData): array
    {
        if ($validatedData['picture'] instanceof File) {
            $originalName = $validatedData['picture']->getFilename();
            $mimeType = $validatedData['picture']->getMimeType();
            $error = null;

            // Manually create a new UploadedFile instance
            $validatedData['picture'] = new UploadedFile(
                $validatedData['picture']->getPathname(), // The full path to the file
                $originalName,        // The original file name
                $mimeType,            // The MIME type
                null,                 // The error status (set to null or appropriate error code)
                true                  // Whether the file was uploaded via HTTP POST
            );
        }
        if ($validatedData['picture'] instanceof UploadedFile) {
            $path = $validatedData['picture']->store('images');
            $this->picture = $path;
        }
        return $validatedData;
    }

    public static function getListOfBooksFilterByGenreId(array $genreId, int $userId): array
    {
        // Fetch books associated with a user and have the specified genre ID
        $books = self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->whereHas('genres', function ($query) use ($genreId) {
            $query->whereIn('genre_id', $genreId);
        })->with(['genres', 'author', 'users' => function ($query) use ($userId) {
            $query->where('user_id', $userId)->withPivot('progression');
        }])->get();

        // Transform books collection to include progression on the main object
        return self::RetrieveProgression($books);
    }

    public static function getListOfBooksFilterByAuthorId(array $authorId, int $userId): array
    {
        $books = self::where('author_id', $authorId)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->with(['genres', 'author', 'users' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->withPivot('progression');
            }])->get();

        // Transform books collection to include progression on the main object
        return self::RetrieveProgression($books);
    }

    public static function getListOfBooksFilterByAuthorIdAndGenreId(array $authorId, array $genreId, int $userId): array
    {
        $books = self::where('author_id', $authorId)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genre_id', $genreId);
            })->with(['genres', 'author', 'users' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->withPivot('progression');
            }])->get();
        return self::RetrieveProgression($books);

    }

    /**
     * Returns the number of books that have been started by a specific user.
     *
     * @param int $userId The ID of the user.
     * @return int The number of books started by the user.
     *
     * @see Book
     * @see User
     * @see Builder::whereHas
     * @see Builder::where
     * @see Builder::count
     */
    public static function BooksStarted(int $userId): int
    {
        return self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('progression', '>', 0)
                ->Where('progression', '<', 100);
        })->count();
    }

    public static function BooksFinished(int $userId): int
    {
        return self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('progression', '=', 100);
        })->count();
    }

    /**
     * Returns the number of books that have not been started by the specified user.
     *
     * @param int $userId The ID of the user.
     * @return int The number of books that have not been started by the user.
     */
    public static function BooksNotStarted(int $userId): int
    {
        return self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('progression', 0);
        })->count();
    }

    /**
     * @param $date_of_publication
     * @return string
     */
    public function getFormattedDate($date_of_publication): string
    {
        $dateString = $date_of_publication;

        try {
            // Try to create a DateTime object directly from the date string
            $dateTime = new DateTime($dateString);

            // Format the DateTime object to ensure it's in the correct MySQL date format
            $formattedDate = $dateTime->format('Y-m-d');
        } catch (Exception $e) {
            // Try to create a DateTime object using createFromFormat method
            $dateTime = DateTime::createFromFormat('d/m/Y', $dateString);

            if ($dateTime) {
                $formattedDate = $dateTime->format('Y-m-d');
            } else {
                throw new \Error("Error: Invalid date format.");
            }
        }
        return $formattedDate;
    }

    /**
     * @param int $authorId
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public static function getListOfBooksBasedOnAuthor(int $authorId): \Illuminate\Database\Eloquent\Collection|array
    {
        return self::where('author_id', $authorId)
            ->where('user_id', null)->get();
    }

    /**
     * Retrieve books associated with a user that have not been completed,
     * ordered by their creation date in descending order.
     *
     * @param int $userId The ID of the user.
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     *
     * @method static Builder|self whereHas(string $relation, \Closure $callback)
     * @method static Builder|self orderBy(string $column, string $direction = 'asc')
     * @method static \Illuminate\Support\Collection get()
     */
    public static function getBooksNotFinishedByUserOrderByDateCreationDesc(int $userId): array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
    {
        return self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->where('progression', '<', 100);
        })
            ->with(['genres', 'author', 'users' => function($query) use ($userId){
                $query->where('user_id', $userId)
                    ->select('users.id');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(self::getUserProgression($userId));
    }

    /**
     * Retrieve a collection of books associated with a specific user, ordered by creation date in descending order.
     *
     * @param int $userId Identifier of the user whose books are to be retrieved.
     * @return \Illuminate\Support\Collection A collection of books with associated genres and author information.
     *
     * @method static Builder|Book whereHasUserId($value)
     * @method static Builder|Book withGenres()
     * @method static Builder|Book withAuthor()
     */
    public static function getBooksFromUserOrderByDateCreationDesc(int $userId): \Illuminate\Support\Collection
    {
        return self::whereHas('users', function ($query) use ($userId){
            $query->where('user_id', $userId);
        })
            ->with(['genres', 'author'])
            ->get()
            ->map(self::getUserProgression($userId));
    }

    public static function getBooksFromUserOrderByRatingDesc(int $userId)
    {
        return self::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->leftJoin('book_rating', 'books.id', '=', 'book_rating.book_id') // Join with the BookRating table
            ->select('books.*') // Select all fields from books table
            ->with(['genres', 'author', 'ratings'])
            ->orderBy('book_rating.rating', 'desc') // Order by the rating column in book_ratings
            ->get()
            ->map(self::getUserProgression($userId));
    }
}
