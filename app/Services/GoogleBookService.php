<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
            'genre' => $data['volumeInfo']['categories'] ?? 'No title available',
        ];
    }
}
