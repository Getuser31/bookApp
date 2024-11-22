<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Book|null $book
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereBookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUserId($value)
 * @mixin \Eloquent
 */
class Notes extends Model
{
    use HasFactory;

    protected $fillable = [
        'content', 'user_id', 'book_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }


    /**
     * @param int $user_id
     * @param int $book_id
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public static function getNotesForBookAndUser(int $user_id, int $book_id): \Illuminate\Database\Eloquent\Collection|array
    {
        return self::whereHas('user', function ($query) use ($user_id) {
            $query->where('id', $user_id);
        })->whereHas('book', function ($query) use ($book_id) {
            $query->where('id', $book_id);
        })->get();
    }
}
