@extends('index')

@section('content')
    <div class="p-4">

        <h1>Welcome to your Library</h1>
        <div class="container mx-auto mt-4">
            <div class="flex justify-end space-x-4 relative">
                <button id="clearFilter" type="button"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Clear Filter
                </button>
                <button id="dropdownCategory" data-dropdown-toggle="dropdown"
                        class="text-blue-500 bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                        type="button">
                    Filter by genre
                </button>
                <button id="dropdownAuthor" data-dropdown-toggle="dropdown"
                        class="text-blue-500 bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center inline-flex items-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                        type="button">
                    Filter by author
                </button>
            </div>

            <!-- Dropdown menu genre-->
            <div id="dropdownCategoryMenu"
                 class="z-10 hidden absolute right-0 mt-2 w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                    Genre
                </h6>
                <ul class="space-y-2 text-sm" aria-labelledby="dropdownCategoryMenu">
                    @foreach($genres as $genre)
                        <li class="flex items-center">
                            <input id="{{$genre->name}}" type="checkbox" value="{{$genre->id}}"
                                   class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 genre-checkbox"/>

                            <label for="{{$genre->name}}"
                                   class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{$genre->name}}
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
            <!-- Dropdown menu author-->
            <div id="dropdownAuthorMenu"
                 class="z-10 hidden absolute right-0 mt-2 w-56 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
                <h6 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                    Authors
                </h6>
                <ul class="space-y-2 text-sm" aria-labelledby="dropdownAuthorMenu">
                    @foreach($authors as $author)
                        <li class="flex items-center">
                            <input id="{{$author->name}}" type="checkbox" value="{{$author->id}}"
                                   class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 author-checkbox"/>
                            <label for="{{$author->name}}"
                                   class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{$author->name}}
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

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
            <tbody id="book-table-body">
            @foreach($books as $book)
                <tr id="book-row-{{$book->id}}" class="border-b border-gray-200 dark:border-gray-700"
                    data-genre-id="{{ $book->genre_id }}" data-author-id="{{ $book->author->id }}">
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">
                        <a href="{{route('book.show',  $book->id)}}"> {{ ($book->title)}}</a></td>
                    <td class="px-6 py-4">{{ ($book->date_of_publication)}}</td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->author->name }}</td>
                    <td class="px-6 py-4 w-1/4">{{\Illuminate\Support\Str::limit($book->description, 200)}}</td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->genre->name }}</td>
                    <td class="px-6 py-4">{{ $book->collection->name ?? '' }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->pivot->progression }}
                        %
                    </td>
                    <td class="px-6 py-4">
                        <form class="deleteBookForm" data-bookid="{{$book->id}}">
                            @csrf
                            @method('DELETE')
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <input type="hidden" id="bookId" value="{{$book->id}}">
                            <button type='submit' class="h-8 w-8 text-red-500">
                                <svg class="h-8 w-8 text-red-500" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon
                                        points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Render pagination links -->
    {{ $books->links('vendor.pagination.tailwind') }}

    <div>
        <a href="{{route('book.addBook')}}"
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add A book</a>
    </div>

    <script>
        //Delete entry
        let deleteForms = document.querySelectorAll('.deleteBookForm');

        deleteForms.forEach((form) => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Retrieve the CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const bookId = this.dataset.bookid;

                //AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '/removeBook/' + bookId, true); // update this to your delete route
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log('Book deleted');
                        let row = document.getElementById('book-row-' + bookId);
                        row.parentNode.removeChild(row);
                    }
                }
                xhr.send();
            });
        });
    </script>

    <script>
        // Filtering Menu
        document.addEventListener('DOMContentLoaded', function () {
            const filterButton = document.getElementById('dropdownCategory');
            const dropdownMenu = document.getElementById('dropdownCategoryMenu');

            filterButton.addEventListener('click', function () {
                dropdownMenu.classList.toggle('hidden'); // Toggle visibility
            });

            document.addEventListener('click', function (event) {
                // Close dropdown if clicked outside
                if (!filterButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        });
        // Dropdown - Authors
        const filterAuthorButton = document.getElementById('dropdownAuthor');
        const dropdownAuthorMenu = document.getElementById('dropdownAuthorMenu');

        filterAuthorButton.addEventListener('click', function () {
            dropdownAuthorMenu.classList.toggle('hidden'); // Toggle visibility
        });

        document.addEventListener('click', function (event) {
            // Close dropdown if clicked outside
            if (!filterAuthorButton.contains(event.target) && !dropdownAuthorMenu.contains(event.target)) {
                dropdownAuthorMenu.classList.add('hidden');
            }
        });
        // Filtering Menu
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const genreCheckboxes = document.querySelectorAll('.genre-checkbox');
            const authorCheckboxes = document.querySelectorAll('.author-checkbox');
            const clearFilters = document.getElementById('clearFilter');

            clearFilters.addEventListener('click', resetFilters);

            function resetFilters() {
                document.querySelectorAll('#book-table-body tr')
                    .forEach(row => {
                        row.style.display = '';
                    })

                genreCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });

                authorCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                })
            }

            let selectedGenres = [];
            let selectedAuthors = [];

            genreCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', filterBooks);
            });

            authorCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', filterBooks);
            });

            function filterBooks() {
                selectedGenres = Array.from(genreCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                selectedAuthors = Array.from(authorCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                document.querySelectorAll('#book-table-body tr')
                    .forEach(row => {
                        const genreId = row.getAttribute('data-genre-id');
                        const authorId = row.getAttribute('data-author-id');

                        if ((!selectedGenres.length || selectedGenres.includes(genreId)) &&
                            (!selectedAuthors.length || selectedAuthors.includes(authorId))) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
            }
        });
    </script>
@endsection
