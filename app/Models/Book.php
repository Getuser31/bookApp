<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

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
        'collection_id'
    ];

    public function storeFromRequest(array $validatedData): void
    {
        $this->title = $validatedData['title'];
        $this->description = $validatedData['description'];
        $this->date_of_publication = $validatedData['date_of_publication'];
        $this->collection_id = $validatedData['collection_id'] ?? null;
        $this->author_id = $validatedData['author_id'];
        $this->genre_id = $validatedData['genre_id'];
        $this->save();
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

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }
}
