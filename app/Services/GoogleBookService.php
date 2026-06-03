<?php

namespace App\Services;

use App\Http\Requests\StoreBookPost;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class GoogleBookService
 *
 * A service class for interacting with the Google Books API and processing book data.
 */
class GoogleBookService
{
    protected Client $client;
    protected string $id;

    private string $url = 'https://www.googleapis.com/books/v1/volumes/';

    private static string $searchUrl = 'https://www.googleapis.com/books/v1/volumes';

    public function __construct(string $id)
    {
        $this->client = new Client();
        $this->id = $id;
    }

    /**
     * @throws GuzzleException
     */
    public static function searchBooks(string $title, string $author = '', string $language = 'fr', int $startIndex = 0, int $maxResults = 30): array
    {
        $client = new Client();

        $query = 'intitle:' . str_replace(' ', '+', $title);
        if (!empty($author)) {
            $query .= '+inauthor:' . str_replace(' ', '+', $author);
        }

        $params = [
            'q'            => $query,
            'langRestrict' => $language,
            'startIndex'   => $startIndex,
            'maxResults'   => $maxResults,
        ];

        if ($apiKey = config('services.google.books_api_key')) {
            $params['key'] = $apiKey;
        }

        $response = $client->request('GET', self::$searchUrl, ['query' => $params]);

        return json_decode($response->getBody(), true) ?? [];
    }

    /**
     * @throws GuzzleException
     */
    public function getBookData(): array
    {
        $params = [];
        if ($apiKey = config('services.google.books_api_key')) {
            $params['query'] = ['key' => $apiKey];
        }

        $url = $this->url . $this->id;
        $response = $this->client->request('GET', $url, $params);
        $data = json_decode($response->getBody(), true);

        return [
            'id' => $data['id'],
            'title' => $data['volumeInfo']['title'],
            'authors' => $data['volumeInfo']['authors'],
            'date_of_publication' => $data['volumeInfo']['publishedDate'],
            'description' => $data['volumeInfo']['description'] ?? null,
            'picture' => $data['volumeInfo']['imageLinks']['thumbnail'] ?? null,
            'genres' => $data['volumeInfo']['categories'] ?? 'No genre available',
            'google_id' => $data['id']
        ];
    }

    /**
     * Process the genre of a book.
     *
     * If the genre is an empty string, return the first genre from the book data.
     * If the genre is a string, parse and clean it.
     * If the genre is an array, iterate through each genre and parse and clean it.
     * Remove any duplicates and empty values from the genres.
     * Fetch or create genre IDs for each genre name.
     *
     * @param string|array $genre The genre(s) of the book.
     * @return string|array The processed genre(s) of the book.
     */
    public function processGenre(string|array $genre): string|array
    {
        // If genre is an empty string, return the first genre from book data
        if ($genre === '') {
            return '';
        }

        $genres = is_string($genre) ? [$genre] : $genre;

        // Parse and clean genres
        $genres = array_reduce($genres, function ($carry, $item) {
            $parts = array_map('trim', preg_split('/[\/&]/', $item));
            return array_merge($carry, $parts);
        }, []);

        // Remove duplicates and empty values
        $genres = array_values(array_filter(array_unique($genres)));

        // Fetch or create genre IDs
        return array_map(function ($genreName) {
            return Genre::firstOrCreate(['name' => $genreName])->id;
        }, $genres);
    }

    /**
     * @throws GuzzleException
     * @throws ValidationException
     * @throws Exception
     */
    public function storeBook(string $id): Book
    {
        //CHECK IF ALREADY STORED
        if ($this->checkIfAlreadyStored($id) === true) {
            $book = Book::where('google_id', $id)->whereNull('user_id')->first()
                ?? Book::where('google_id', $id)->first();
            $user = Auth::user();

            if (empty($book->picture)) {
                try {
                    $data = $this->getBookData();
                    if (!empty($data['picture'])) {
                        $picture = $this->downloadPicture(str($data['picture']));
                        if ($picture instanceof File) {
                            $book->FormatUploadedFile(['picture' => $picture]);
                            $book->save();
                        }
                    }
                } catch (\Throwable $e) {
                    // Non-critical — proceed without refreshing picture
                }
            }

            if ($user && !$user->books()->where('books.id', $book->id)->exists()) {
                $user->books()->attach($book);
            }
            return $book;
        }

        //PREPARE TO STORE NEW
        $data = $this->getBookData($id);
        $data = $this->GetFormatedDate($data);

        $authorId = $this->processAuthors($data['authors']);
        $data['author_id'] = $authorId;

        $genres = $this->processGenre($data['genres']);
        $data['genres'] = $genres;


        if ($data['picture'] !== null) {
            $picture = $this->processPicture(str($data['picture']));
            $data['picture'] = $picture;
        }

        $validator = Validator::make($data, (new StoreBookPost())->rules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validatedData = $validator->validated();

        $book = new Book();
        $book->storeFromRequest($validatedData);

        return $book;
    }

    /**
     * Checks if a book with the given Google ID already exists in the database.
     *
     * @param string $id The Google ID of the book.
     * @return bool Returns true if a book with the given Google ID exists, otherwise returns false.
     */
    public function checkIfAlreadyStored(string $id): bool
    {
        return Book::where('google_id', $id)->exists();
    }

    /**
     * @param $authors
     * @return int|mixed
     */
    public function processAuthors($authors): mixed
    {
        $author = $authors;
        if (is_array($author)) {
            $author = $author[0];
        }
        $author = Author::firstOrCreate(['name' => $author]);
        return $author->id;
    }

    /**
     * @throws GuzzleException
     */
    public function processPicture(string $picture): RedirectResponse|File
    {
        $file = $this->downloadPicture($picture);
        if ($file) {
            return $file;
        }
        return back()->withErrors(['picture' => 'Unable to download picture']);
    }

    private function downloadPicture(string $pictureUrl): ?File
    {
        $dir = storage_path('app/images');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $tmpFilePath = $dir . '/tmp_' . uniqid() . '.jpg';
        $response = $this->client->get($pictureUrl, ['sink' => $tmpFilePath]);
        if ($response->getStatusCode() === 200) {
            return new File($tmpFilePath);
        }
        @unlink($tmpFilePath);
        return null;
    }

    /**
     * Formats the date of publication in the given data array and returns the updated array.
     *
     * @param array $data The data array containing the date of publication.
     * @return array The updated data array with the formatted date of publication.
     * @throws Exception
     */
    public function GetFormatedDate(array $data): array
    {
        // Create a DateTime object from the date string
        $date = new \DateTime($data['date_of_publication']);
        // Format the date
        $formattedDate = $date->format('d/m/Y');
        $data['date_of_publication'] = $formattedDate;
        return $data;
    }
}
