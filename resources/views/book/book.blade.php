@extends('index')


@section('content')

    <h1>{{$book->title}}</h1>

    <p>
    <ul>
        <li><b>Date de publication: </b>{{ $book->date_of_publication}}</li>
        <li><b>Auteur: </b>{{ $book->author->name }}</li>
        <li><b>Synopsis: </b><p>{{$book->description}}</p></li>
        <li><b>Genre: </b>{{ $book->genre->name }}</li>
        @if($book->collection)<li><b>Collection</b>{{ $book->collection->name }}</li> @endif
    </ul><br>


@endsection
