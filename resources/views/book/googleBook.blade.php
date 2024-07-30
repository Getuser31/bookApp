@extends('index')
@section('content')
    <h1>{{ $title }}</h1>
    Author: {{ $author }} <br/>
    Date Of Publication: {{ $dateOfPublication }} <br/>
    Genre: {{ collect($genre)->pluck('name')->implode(' / ') }} <br/>
    Description: <p>{{ $description }}</p>
    <img src="{{ $thumbnail }}" alt="picture">
    <a href="{{route('book.googleBookStore', ['id' => $id])}}" id="addBook" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Book</a>

@endsection
