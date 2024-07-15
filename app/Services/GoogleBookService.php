<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;

class GoogleBookService
{
    protected Client $client;
    protected string $id;

    private string $url = 'https://www.googleapis.com/books/v1/volumes/';

    public function __construct(string $id)
    {
        $this->client = new Client();
        $this->id = $id;
    }

    /**
     * @throws GuzzleException
     */
    public function getBookData(): array
    {
        $url = $this->url . $this->id;
        $response = $this->client->request('GET', $url);
        $data = json_decode($response->getBody(), true);

        return [
            'id' => $data['id'],
            'title' => $data['volumeInfo']['title'],
            'authors' => $data['volumeInfo']['authors'],
            'publishedDate' => $data['volumeInfo']['publishedDate'],
            'description' => $data['volumeInfo']['description'],
            'picture' => $data['volumeInfo']['imageLinks']['thumbnail'] ?? null,
            'genres' => $data['volumeInfo']['categories'] ?? 'No title available',
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function processGenre($genre): string|array
    {
        if (is_array($genre)) {
            $genre = implode('', $genre);
        } elseif ($genre == '') {
            $genre = $this->getBookData();
            return $genre['genre'][0];
        }

        $arr = explode("/", $genre);

        if (is_array($arr)) {
            $genreId = [];
            foreach ($arr as $genre) {
                $genre = Genre::firstOrCreate(['name' => $genre]);
                $genreId[] = $genre->id;
            }
            return $genreId;
        }

        return $genre;

    }

    /**
     * @throws GuzzleException
     */
    public function storeBook(string $id): Book
    {
        if ($this->checkIfAlreadyStored($id) === true) {
            return Book::where('google_id', $id)->first();
        }
        $data = $this->getBookData($id);

        $author = $data['authors'];
        if (is_array($author)) {$author = $author[0];}
        $author = Author::firstOrCreate(['name' => $author]);
        $authorId = $author->id;

        $genres = $this->processGenre($data['genres']);


        $book = new Book();
        $book->title = $data['title'];
        $book->author_id = $authorId;
        $book->description = $data['description'];
        $book->picture = $data['picture'];
        $book->google_id = $data['id'];
        $book->date_of_publication = $data['publishedDate'];
        $book->save();
        $book->genres()->sync($genres);


        $user = Auth::user();
        $user->books()->attach($book);

        return $book;
    }

    public function checkIfAlreadyStored(string $id): bool
    {
        return Book::where('google_id', $id)->exists();
    }
}
