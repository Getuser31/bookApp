<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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


    public static function getNotesForBookAndUser(int $user_id, int $book_id)
    {
        return self::whereHas('user', function ($query) use ($user_id) {
            $query->where('id', $user_id);
        })->whereHas('book', function ($query) use ($book_id) {
            $query->where('id', $book_id);
        })->get();
    }
}
