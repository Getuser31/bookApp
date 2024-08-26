<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

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
