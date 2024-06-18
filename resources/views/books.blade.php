@extends('index')

@section('content')

@foreach($books as $book)
    <h1>{{$book->title}}</h1>
    <ul>
        <li><b>Date de publication: </b>{{ $book->date_of_publication}}</li>
        <li><b>Auteur: </b>{{ $book->author->name }}</li>
        <li><b>Synopsis: </b><p>{{$book->description}}</p></li>
        <li><b>Genre: </b>{{ $book->genre->name }}</li>
        @if($book->collection)<li><b>Collection</b>{{ $book->collection->name }}</li> @endif
    </ul><br>
@endforeach

<!-- Render pagination links -->
{{ $books->links('vendor.pagination.tailwind') }}

@endsection
