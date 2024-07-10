<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\BookController;
use App\Models\Genre;
use App\Services\GoogleBookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    private BookController $bookController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookController = new BookController();
    }

    /**
     * Test for processGenre method if string is passed as $genre and it's not empty
     * */
    public function testProcessGenreWithStringAndNotEmpty(): void
    {
        $ids = $this->bookController->processGenre('genre1/genre2/genre3', 1);
        $genres = Genre::find($ids);

        $this->assertCount(3, $genres);
        $genres->each(function ($genre) {
            $this->assertContains($genre->name, ['genre1', 'genre2', 'genre3']);
        });
    }

    /**
     * Test for processGenre method if string is passed as $genre and it's empty
     * */
    public function testProcessGenreWithStringAndEmpty(): void
    {
        $googleBookService = Mockery::mock(GoogleBookService::class);
        $googleBookService->shouldReceive('getBookData')
            ->andReturn(['genre' => ['genre4', 'genre5', 'genre6']]);

        app()->instance(GoogleBookService::class, $googleBookService);

        $id = $this->bookController->processGenre('', 1);

        $this->assertEquals('genre4', $id);
    }

    /**
     * Test for processGenre method if array is passed as $genre
     * */
    public function testProcessGenreWithArray(): void
    {
        $genre = $this->bookController->processGenre(['genre1', 'genre2'], 1);
        $this->assertEquals('genre1genre2', $genre);
    }
}
