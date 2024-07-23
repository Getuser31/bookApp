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
            'description' => $data['volumeInfo']['description'],
            'picture' => $data['volumeInfo']['imageLinks']['thumbnail'] ?? null,
            'genres' => $data['volumeInfo']['categories'] ?? 'No title available',
            'google_id' => $data['id']
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
     * @param array $data
     * @return array
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
