@extends('index')

@section('content')
    <h2>@if($book) Edit @else Create @endif Book</h2>
    <form method="post" action="@if($book) {{route('admin.book.update', $book->id)}} @else {{route('admin.book.store')}} @endif" class="w-full max-w-lg" id="Book">
        <div class="flex flex-wrap -mx-3 mb-6">
            @csrf
            @if($book)
                @method('PUT')
            @endif
            <div class="w-full px-3">
                <label for="title"
                       class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                    Title
                </label>
                <input id="title" type="text" name="title" value="{{$book->title ?? ''}}"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white">
                @error('title')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full px-3">
                <label for="description"
                       class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                    description
                </label>
                <input id="description" type="text" name="description" value="{{$book->description ?? ''}}"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white">
                @error('description')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full px-3">
                <label for="date_of_publication"
                       class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                    Date Of Publication:
                </label>
                <input type="text" id="date" name="date_of_publication" value="{{$book->date_of_publication ?? ''}}"
                       placeholder="dd/mm/YYYY"
                       class="appearance-none block w-full bg-gray-200 text-gray-700 border border-red-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white">
                <p id="output" class="text-red-500"></p>
                @error('date_of_publication')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full px-3">
                <label for="author_id"
                       class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                    Author
                </label>
                <svg class="w-2 h-2 absolute top-0 right-0 m-4 pointer-events-none" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 412 232">
                    <path d="M206 171.144L42.678 7.822a29.762 29.762 0 00-42.224 0C-3.568 13.271-3.567
    26.716.241 36.155L175.855 212.34a29.763 29.763 0 0060.289 0l175.634-176.185c3.802-3.802
    3.801-10.246.04-14.044a29.767 29.767 0 00-42.241-.04L206 171.144z"
                          fill="#648299" fill-rule="nonzero"/>
                </svg>
                <select name="author_id" class="border border-gray-300 rounded-full text-gray-600 h-10 pl-5 pr-10 bg-white
  hover:border-gray-400 focus:outline-none appearance-none">
                    <option  value="{{$book->author->id ?? ''}}"> {{$book->author->name ?? 'Select an author'}}</option>
                    @foreach($authors as $author)
                        <option value="{{$author->id}}">{{ $author->name }}</option>
                    @endforeach
                </select>
                @error('author_id')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="w-full px-3">
                <label for="genre"
                       class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                    Genre
                </label>
                <svg class="w-2 h-2 absolute top-0 right-0 m-4 pointer-events-none" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 412 232">
                    <path d="M206 171.144L42.678 7.822a29.762 29.762 0 00-42.224 0C-3.568 13.271-3.567
    26.716.241 36.155L175.855 212.34a29.763 29.763 0 0060.289 0l175.634-176.185c3.802-3.802
    3.801-10.246.04-14.044a29.767 29.767 0 00-42.241-.04L206 171.144z"
                          fill="#648299" fill-rule="nonzero"/>
                </svg>
                <select name="genre_id" class="border border-gray-300 rounded-full text-gray-600 h-10 pl-5 pr-10 bg-white
  hover:border-gray-400 focus:outline-none appearance-none">
                    <option  value="{{$book->genre->id ?? ''}}">{{$book->genre->name ?? 'Select a genre'}}</option>
                    @foreach($genres as $genre)
                        <option value="{{$genre->id}}">{{ $genre->name }}</option>
                    @endforeach
                </select>
                @error('genre_id')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>


            <div class="w-full px-3">
                <label for="collection"
                       class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                    Collection
                </label>
                <svg class="w-2 h-2 absolute top-0 right-0 m-4 pointer-events-none" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 412 232">
                    <path d="M206 171.144L42.678 7.822a29.762 29.762 0 00-42.224 0C-3.568 13.271-3.567
    26.716.241 36.155L175.855 212.34a29.763 29.763 0 0060.289 0l175.634-176.185c3.802-3.802
    3.801-10.246.04-14.044a29.767 29.767 0 00-42.241-.04L206 171.144z"
                          fill="#648299" fill-rule="nonzero"/>
                </svg>
                <select name="collection_id" class="border border-gray-300 rounded-full text-gray-600 h-10 pl-5 pr-10 bg-white
  hover:border-gray-400 focus:outline-none appearance-none">
                    <option  value="{{$book->collection->id ?? ''}}">{{$book->collection->name ?? 'Select a Collection'}}</option>
                    @foreach($collections as $collection)
                        <option>{{ $collection->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('collection_id')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>



        <button type="submit" value="Submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit
        </button>
    </form>

    <script>
        document.querySelector("#Book").addEventListener("submit", function (event) {

            let dateInput = document.querySelector("#date").value;
            let datePattern = /^([0-2][0-9]|(3)[0-1])(\/)(((0)[0-9])|((1)[0-2]))(\/)\d{4}$/;

            if (!datePattern.test(dateInput)) {
                event.preventDefault(); // Prevent form from submitting to server only if date is invalid
                document.querySelector("#output").innerText = "The date is invalid";
            }
        });
    </script>

@endsection
