<?php

namespace App\Services;

use App\Http\Requests\StoreBookPost;
use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
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
     * @throws GuzzleException
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
            return Book::where('google_id', $id)->first();
        }

        //PREPARE TO STORE NEW
        $data = $this->getBookData($id);
        $data = $this->GetFormatedDate($data);

        $authorId = $this->processAuthors($data['authors']);
        $data['author_id'] = $authorId;

        $genres = $this->processGenre($data['genres']);
        $data['genres'] = $genres;

        $picture = $this->processPicture(str($data['picture']));
        $data['picture'] = $picture;

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
     * Processes the picture by downloading it and returning a RedirectResponse or a File object.
     *
     * @param string $picture The URL of the picture to be processed.
     * @return RedirectResponse|File The processed picture as a RedirectResponse if successful, or a File object if unsuccessful.
     */
    public function processPicture(string $picture): RedirectResponse|File
    {
        function downloadPicture(string $pictureUrl): ?string
        {
            $client = new Client();
            $tmpFilePath = storage_path('app/images/temporary_picture.jpg');
            $response = $client->get($pictureUrl, ['sink' => $tmpFilePath]);

            if ($response->getStatusCode() === 200) {
                return $tmpFilePath;
            }
            return null;
        }

        function processDownloadedPicture(string $filePath): File
        {
            return new File($filePath);
        }

        // Download the picture with Guzzle
        $pictureUrl = $picture;
        $downloadedFilePath = downloadPicture($pictureUrl);
        if ($downloadedFilePath) {
            return processDownloadedPicture($downloadedFilePath);
        } else {
            return back()->withErrors(['picture' => 'Unable to download picture']);
        }
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
