<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminController
{
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.index');
    }

    public function handleGenre(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $genres = Genre::all();
        return view('admin.handleGenre',[
        'genres' => $genres]);
    }

    public function editGenre(Int $id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $genre = Genre::find($id);
        return view('admin.formGenre',compact('genre'));
    }

    public function createGenre(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.formGenre', ['genre' => null]);
    }

    public function updateGenre(Request $request, Int $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255' // @todo move this logic out of the controller
        ]);

        $genre = Genre::findOrFail($id);
        $genre->name = $validatedData['name'];
        $genre->save();

        return redirect()->route('admin.genre');
    }

    public function storeGenre(Request $request): RedirectResponse
    {
        $genre = new Genre();
        $validatedData = $request->validate([
            'name' => 'required|max:255' // @todo move this logic out of the controller
        ]);
        $genre->name = $validatedData['name'];
        $genre->save();
        return redirect()->route('admin.genre');
    }

    public function handleAuthor(Request $request)
    {

    }

    public function handleBook(Request $request)
    {

    }

    public function handleCollection(Request $request)
    {

    }
}
