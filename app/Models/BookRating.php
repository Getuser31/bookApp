<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookRating extends Pivot
{
    protected $table = 'book_rating';

    protected $fillable = ['book_id', 'user_id', 'rating_id'];
}
