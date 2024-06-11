<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Collection;
use App\Models\Genre;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers
 */
class AdminController
{
    /**
     * Render the admin index view.
     *
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function index(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.index');
    }

    /**
     * Handle genre functionality
     *
     * This method retrieves all genres from the database and returns
     * a view with the genres data.
     *
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function handleGenre(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $genres = Genre::all();
        return view('admin.handleGenre', [
            'genres' => $genres]);
    }

    /**
     * Edit a genre.
     *
     * @param int $id The ID of the genre to edit.
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application The view to display the genre form.
     */
    public function editGenre(int $id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $genre = Genre::find($id);
        return view('admin.formGenre', compact('genre'));
    }

    public function createGenre(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.formGenre', ['genre' => null]);
    }

    public function updateGenre(Request $request, int $id): RedirectResponse
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

    public function deleteGenre(int $id): RedirectResponse
    {
        $genre = Genre::findOrFail($id);

        $genre->delete();
        return redirect()->route('admin.genre');
    }

    /**
     * Handle author request.
     *
     * @param Request $request The HTTP request.
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application The view to handle the author request.
     */
    public function handleAuthor(Request $request): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $author = Author::all();
        return view('admin.handleAuthor', ['authors' => $author]);
    }

    /**
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function createAuthor(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        return view('admin.formAuthor', ['author' => null]);
    }

    /**
     * Edit an author.
     *
     * @param int $id The ID of the author to edit.
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application The updated author form view.
     */
    public function editAuthor(int $id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
       $author = Author::findOrFail($id);
       return view('admin.formAuthor', ['author' => $author]);
    }

    /**
     * Store a new author.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeAuthor(Request $request): RedirectResponse
    {
        $author = new Author();
        $validatedData = $request->validate(['name' => 'required|max:255']);
        $author->name = $validatedData['name'];
        $author->save();

        return redirect(route('admin.author'));

    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function updateAuthor(Request $request, int $id): RedirectResponse
    {
        $author = Author::findOrFail($id);
        $validatedData = $request->validate(['name' => 'required|max:255']);
        $author->name = $validatedData['name'];
        $author->save();

        return redirect(route('admin.author'));
    }

    public function deleteAuthor(int $id): RedirectResponse
    {
        $author = Author::findOrFail($id);

        $author->delete();

        return redirect()->route('admin.author');
    }

    /**
     * Handle collection
     *
     * Retrieve all collections from the database and return a view with the collections
     *
     * @return View
     */
    public function handleCollection(): View
    {
        $collections = Collection::all();
        return view('admin.handleCollection', ['collections' => $collections]);
    }

    /**
     * Create a new collection.
     *
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function createCollection(): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
       return view('admin.formCollection', ['collection' => null]);
    }

    /**
     * Edit a collection.
     *
     * @param int $id The ID of the collection to be edited.
     * @return Factory|Application|View|\Illuminate\Contracts\Foundation\Application
     */
    public function editCollection(int $id): Factory|Application|View|\Illuminate\Contracts\Foundation\Application
    {
        $collection = Collection::findOrFail($id);
        return view('admin.formCollection', compact('collection'));

    }

    /**
     * Store a new collection.
     *
     * @param Request $request The incoming request object.
     * @return RedirectResponse The redirect response after storing the collection.
     */
    public function storeCollection(Request $request): RedirectResponse
    {
        $collection = new Collection();
        $validatedData = $request->validate(['name' => 'required|max:255']);
        $collection->name = $validatedData['name'];
        $collection->save();

        return redirect()->route('admin.collection');

    }

    /**
     * Update an existing collection.
     *
     * @param Request $request The incoming request object.
     * @param int $id The ID of the collection to be updated.
     * @return RedirectResponse The redirect response after updating the collection.
     */
    public function updateCollection(Request $request, int $id): RedirectResponse
    {
        $collection = Collection::findOrFail($id);
        $validatedData = $request->validate(['name' => 'required|max:255']);
        $collection->name = $validatedData['name'];
        $collection->save();

        return redirect(route('admin.collection'));
    }

    public function deleteCollection(int $id): RedirectResponse
    {
        $collection = Collection::findOrFail($id);

        $collection->delete();

        return redirect()->route('admin.collection');

    }

    public function handleBook(Request $request)
    {

    }


}
