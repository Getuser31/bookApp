<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Request;

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

        return response()->json(['message' => 'Genre has been created']);
    }

    public function deleteGenre(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);

        $genre->delete();
        return response()->json(['message' => 'Genre has been deleted']);
    }
}
