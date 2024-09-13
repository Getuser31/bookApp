<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int $id
 * @property int $book_id
 * @property int $user_id
 * @property int $rating
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|BookRating newModelQuery()
 * @method static Builder|BookRating newQuery()
 * @method static Builder|BookRating query()
 * @method static Builder|BookRating whereBookId($value)
 * @method static Builder|BookRating whereCreatedAt($value)
 * @method static Builder|BookRating whereId($value)
 * @method static Builder|BookRating whereRating($value)
 * @method static Builder|BookRating whereUpdatedAt($value)
 * @method static Builder|BookRating whereUserId($value)
 * @property-read \App\Models\Book|null $book
 * @property-read \App\Models\User|null $user
 * @mixin \Eloquent
 */
class BookRating extends Pivot
{
    protected $table = 'book_rating';

    protected $fillable = ['book_id', 'user_id', 'rating_id'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getRating(int $book_id, int $user_id): Model|Builder|BookRating|null
    {
        return self::where('book_id', $book_id)->where('user_id', $user_id)->first();
    }

    /**
     * Returns the average rating for books associated with the given user ID.
     *
     * @param int $user_id The ID of the user.
     * @return float The average rating for the user's books.
     */
    public static function getAverageBookRating(int $user_id): float
    {
        return self::where('user_id', $user_id)->avg('rating');
    }

}
