@extends('index')

@section('content')
    @if(!$books->items())
        <h3 class="text-xl font-semibold text-gray-500">It look like you don't have any book right now</h3>

        @include('book.searchBookForm')
    @else

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
                <li><b>Genre: </b>@foreach($book->genres as $genre) {{ $genre->name }} @endforeach</li>
                @if($book->collection)
                    <li><b>Collection</b>{{ $book->collection->name }}</li>
                @endif
                <li><b>Progression:</b> {{$book->pivot->progression}}</li>
            </ul>
            <br>
        </div>
    @endforeach
    @endif


    <!-- Render pagination links -->
    {{ $books->links('vendor.pagination.tailwind') }}

@endsection
