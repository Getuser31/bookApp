@extends('index')
@push('styles')
    @vite('resources/css/book/addBook.css')
@endpush

@section('content')
    <section>
        <h2 class="text-2xl font-semibold text-gray-900">Looking for a new book to read?</h2>
        <form method="post" action="" class="space-y-4">
            @csrf
            <div>
                <label for="GoogleBookSearch" class="block text-sm font-medium text-gray-700">Search for a book</label>
                <input
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    type="text"
                    placeholder="Search for a book"
                    id="bookSearch"
                >
            </div>

            <div>
                <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
                <input
                    type="text"
                    id="author"
                    placeholder="Add an author"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
            </div>

            <div>
                <label for="language" class="block text-sm font-medium text-gray-700">Select a language</label>
                <select
                    id="language"
                    name="language"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    @foreach(App\Models\defaultLanguage::all() as $language)
                        <option value="{{$language->id}}">{{$language->language}}</option>
                    @endforeach
                </select>
            </div>

            <button
                id="googleSearchButton"
                type="button"
                value="search"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Search
            </button>
        </form>


        <div id="searchResults"></div>
    </section>

    <script>
        let searchButton = document.getElementById('googleSearchButton');

        searchButton.addEventListener('click', function () {
            let author = document.getElementById('author').value;
            let title = document.getElementById('bookSearch').value;

            const languageSelect = document.getElementById('language');
            // Get the selected <option> element
            const selectedOption = languageSelect.options[languageSelect.selectedIndex];
            // Get the text content of the selected <option>
            const selectedLanguage = selectedOption.text;

            console.log(author);
            initializeBookSearch('bookSearch', 'searchResults', selectedLanguage, title, author);
        });
    </script>
@endsection
