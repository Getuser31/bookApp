<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use Illuminate\Http\JsonResponse;

class AuthorController
{
    public function bookFromAuthor(int $id): JsonResponse
    {
        $books = Book::getListOfBooksBasedOnAuthor($id)->toArray();

        return response()->json([
            'books' => $books
        ]);
    }
}
