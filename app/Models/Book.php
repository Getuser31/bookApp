<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Genre> $genres
 * @property-read int|null $genres_count
 * @method static Builder|Book whereGoogleId($value)
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
        'google_id'
    ];

    public function storeFromRequest(array $validatedData): void
    {
        $this->title = $validatedData['title'];
        $this->description = $validatedData['description'];
        $this->date_of_publication = $validatedData['date_of_publication'];
        $this->collection_id = $validatedData['collection_id'] ?? null;
        $this->author_id = $validatedData['author_id'];
        $this->google_id = $validatedData['google_id'];
        $validatedData = $this->FormatUploadedFile($validatedData);

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
        return $this->belongsToMany(User::class)->withPivot(['progression']);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
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
}
