<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $book_id
 * @property int $user_id
 * @property int $rating
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating whereBookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookRating whereUserId($value)
 * @mixin \Eloquent
 */
class BookRating extends Pivot
{
    protected $table = 'book_rating';

    protected $fillable = ['book_id', 'user_id', 'rating_id'];

    public static function getRating(int $book_id, int $user_id){
        return self::where('book_id', $book_id)->where('user_id', $user_id)->first();
    }

    /**
     * Returns the average rating for books associated with the given user ID.
     *
     * @param int $user_id The ID of the user.
     * @return float The average rating for the user's books.
     */
    public static function getAverageBookRating(int $user_id)
    {
        return self::where('user_id', $user_id)->avg('rating');
    }

}
