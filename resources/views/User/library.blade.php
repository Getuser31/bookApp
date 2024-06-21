@extends('index')

@section('content')
    <div class="p-4">
        <h1>Welcome to your Library</h1>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="table-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">Title</th>
                    <th scope="col" class="px-6 py-3">Date de publication</th>
                    <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">Auteur</th>
                    <th scope="col" class="px-6 py-3">Synopsis:</th>
                    <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">Genre</th>
                    <th scope="col" class="px-6 py-3">Collection</th>
                    <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-800">Progression</th>
                    <th scope="col" class="px-6 py-3">Delete</th>
                </tr>
                </thead>
                <tbody>
                @foreach($books as $book)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800"><a href="{{route('book.show',  $book->id)}}"> {{ ($book->title)}}</a></td>
                        <td class="px-6 py-4">{{ ($book->date_of_publication)}}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->author->name }}</td>
                        <td class="px-6 py-4 w-1/4">{{\Illuminate\Support\Str::limit($book->description, 200)}}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->genre->name }}</td>
                        <td class="px-6 py-4">{{ $book->collection->name ?? '' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->pivot->progression }}%</td>
                        <td class="px-6 py-4"><form id="deleteBook">
                                @csrf
                                @method('DELETE')
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                                <input type="hidden" id="bookId" value="{{$book->id}}">
                                <button type='submit' class="h-8 w-8 text-red-500">
                                    <svg class="h-8 w-8 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon
                                            points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                </button>
                            </form></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Render pagination links -->
    {{ $books->links('vendor.pagination.tailwind') }}

    <div>
        <a href="{{route('book.addBook')}}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add A book</a>
    </div>

    <script>
        document.getElementById('deleteBook').addEventListener('submit', function(e) {
            e.preventDefault();

            // Retrieve the CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const bookId = document.getElementById('bookId').value;

            //AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST',
                '/deleteBook', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    //Remove book from the list dynamically
                }
            }
            xhr.send('&bookId=' + bookId);
        });
    </script>

@endsection
