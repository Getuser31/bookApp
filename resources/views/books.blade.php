@extends('index')

@section('content')

    @foreach($books as $book)
        <h1 class="text-2xl md:text-4xl lg:text-5xl font-bold text-center mb-4">{{$book->title}}</h1>
        <div class="book" style="display: flex; align-items: flex-start;">
            <img src="{{ asset('/'.$book->picture) }}" alt="book picture"
                 style="float: left; max-width: 200px; height: 100%; object-fit: contain; margin-right: 10px;">
            <ul style="flex-grow: 1;">
                <li><b>Date de publication: </b>{{ $book->date_of_publication}}</li>
                <li><b>Auteur: </b>{{ $book->author->name }}</li>
                <li><b>Synopsis: </b>
                    <p>{{$book->description}}</p></li>
                <li><b>Genre: </b>{{ $book->genre->name }}</li>
                @if($book->collection)
                    <li><b>Collection</b>{{ $book->collection->name }}</li>
                @endif
                <li><b>Progression:</b> {{$book->pivot->progression}}</li>
            </ul>
            <br>
        </div>
    @endforeach

    <!-- Render pagination links -->
    {{ $books->links('vendor.pagination.tailwind') }}

@endsection
