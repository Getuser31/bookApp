<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\AuthorRequest;
use App\Http\Requests\GenreRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminController
{
    public function handleGenre(): JsonResponse
    {
        $genres = Genre::all();
        return response()->json([
            'genres' => $genres
        ]);
    }

    public function createGenre()
    {
      //  return view('admin.formGenre', ['genre' => null]);
    }

    public function updateGenre(GenreRequest $request, int $id): JsonResponse
    {
        $validatedData = $request->validated();

        $genre = Genre::findOrFail($id);
        $genre->name = $validatedData['name'];
        $genre->save();

        return response()->json(['message' => 'Genre updated']);
    }

    public function storeGenre(GenreRequest $request): JsonResponse
    {
        $genre = new Genre();
        $validatedData = $request->validated();
        $genre->name = $validatedData['name'];
        $genre->save();

        return response()->json([
            'message' => 'Genre has been created',
            'genre' => $genre
        ]);
    }

    public function deleteGenre(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);

        $genre->delete();
        return response()->json(['message' => 'Genre has been deleted']);
    }

    public function handleAuthors(): JsonResponse
    {
        $authors = Author::all();
        return response()->json(['authors' => $authors]);
    }

    /**
     * Update the specified author's information in the database.
     *
     * @param int $id The unique identifier of the author to be updated.
     * @param AuthorRequest $request The HTTP request containing validated author data.
     * @return JsonResponse The JSON response indicating the result of the update operation.
     */
    public function updateAuthor(int $id, AuthorRequest $request): JsonResponse
    {
        $author = Author::findOrFail($id);
        if (!$author){
            return response()->json(['message' => 'Author not found']);
        }
        if ($request->validated()){
            $author->name = $request->validated()['name'];
            $author->save();
            return response()->json(['message' => 'Author updated']);
        }
        return response()->json(['message' => 'Author not updated']);
    }

    /**
     * Remove the specified author from the database.
     *
     * @param int $id The unique identifier of the author to be deleted.
     * @return JsonResponse The JSON response confirming the deletion of the author.
     */
    public function deleteAuthor(int $id): JsonResponse
    {
        $author = Author::findOrFail($id);
        if (!$author){
            return response()->json(['message' => 'Author not found']);
        }
        $author->delete();
        return response()->json(['message' => 'Author deleted']);
    }

    /**
     * Store a new author record in the database.
     *
     * @param AuthorRequest $request The HTTP request containing validated data for the new author.
     * @return JsonResponse The JSON response confirming the creation of the author.
     */
    public function storeAuthor(AuthorRequest $request): JsonResponse
    {
        $author = new Author();
        $validatedData = $request->validated();
        $author->name = $validatedData['name'];
        $author->save();

        return response()->json([
            'message' => 'Author has been created',
            'author' => $author
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function handleBooks(): JsonResponse
    {
        $books = Book::all();

        return response()->json([
            'books' => $books
        ]);
    }

    /**
     * Delete the specified book from the database.
     *
     * @param int $id The unique identifier of the book to be deleted.
     * @return JsonResponse The JSON response indicating the result of the delete operation.
     */
    public function deleteBook(int $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        try {
            $book->delete();
        } catch (\Exception $e) {
            // Return 500 status for server error
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Return 200 status (default) for success
        return response()->json(['message' => 'Book deleted'], 200);
    }

    /**
     * @return JsonResponse
     */
    public function handleUsers(): JsonResponse
    {
         $users = User::All();
         $roles = Role::all();

         return response()->json([
             'users' => $users,
             'roles' => $roles
         ]);
    }
}
