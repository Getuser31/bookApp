@extends('index')
@push('styles')
    @vite('resources/css/book/addBook.css')
@endpush

@section('content')
    <h1 class="text-4xl text-center font-bold text-blue-500"> Add book to your library</h1>

    <section>
        <h2 class="text-2xl text-left font-bold text-green-600">Search for a book in google Database</h2>

        <input
            class="shadow appearance-none border rounded w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            type="text" placeholder="Search for a book" id="GoogleBookSearch">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <button id="googleSearchButton" type="button" value="search"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Search
        </button>

        <div id="searchApiResults"></div>
    </section>

    <script>
        let searchButton = document.getElementById('googleSearchButton');
        let searchInput = document.getElementById('GoogleBookSearch')
        let resultsDivApi = document.getElementById('searchApiResults'); // Select the results div

        searchButton.addEventListener('click',

            () => {
                const value = searchInput.value;
                if (value) {
                    let url = "https://www.googleapis.com/books/v1/volumes?q=" + value

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            // 'Authorization': `Bearer ${YourToken}`,  // Uncomment this line if you need to send a Bearer token for authorization
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            const books = data.items;
                            resultsDivApi.innerHTML = ''; //clear previous data

                            books.forEach(item => {
                                console.log(item)
                                let resultItem = document.createElement('div');
                                resultItem.classList.add('search-result-item'); // Add a class for CSS styling

                                let titleElement = document.createElement('p');
                                titleElement.textContent = `Title: ${item.volumeInfo.title}`;
                                resultItem.appendChild(titleElement);

                                let authorElement = document.createElement('p');
                                authorElement.textContent = `Author: ${item.volumeInfo.authors ? item.volumeInfo.authors.join(', ') : 'N/A'}`;
                                resultItem.appendChild(authorElement);

                                let dateElement = document.createElement('p');
                                dateElement.textContent = `Date of Publication: ${item.volumeInfo.publishedDate || 'N/A'}`;
                                resultItem.appendChild(dateElement);

                                let genreElement = document.createElement('p');
                                genreElement.textContent = `Genre: ${item.volumeInfo.categories ? item.volumeInfo.categories.join(', ') : 'N/A'}`;
                                resultItem.appendChild(genreElement);

                                let descriptionElement = document.createElement('p');
                                descriptionElement.textContent = `Description: ${ item.volumeInfo.description.length > 250
                                    ? item.volumeInfo.description.substring(0, 247) + '...'
                                    : item.volumeInfo.description || 'N/A'}`;
                                resultItem.appendChild(descriptionElement);

                                if (item.volumeInfo.imageLinks && item.volumeInfo.imageLinks.thumbnail) {
                                    let thumbnailElement = document.createElement('img');
                                    thumbnailElement.src = item.volumeInfo.imageLinks.thumbnail;
                                    thumbnailElement.alt = `Thumbnail for ${item.volumeInfo.title}`;
                                    resultItem.appendChild(thumbnailElement);
                                }

                                document.body.appendChild(resultItem);

                                // Create a hidden form for POST submission
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = '/googleBook';

// Add CSRF token input to the form
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = csrfToken;
                                form.appendChild(csrfInput);

// Append the form to the document body
                                document.body.appendChild(form);

// Make result item clickable and going to book detail page
                                resultItem.addEventListener('click', function () {
                                    const book = {
                                        id: item.id,
                                        title: item.volumeInfo.title,
                                        author: item.volumeInfo.authors,
                                        dateOfPublication: item.volumeInfo.publishedDate,
                                        genre: item.volumeInfo.categories ? item.volumeInfo.categories : '',
                                        description: item.volumeInfo.description,
                                        thumbnail: item.volumeInfo?.imageLinks?.thumbnail ? item.volumeInfo.imageLinks.thumbnail : '',
                                    };

                                    // Clear any previous book data inputs
                                    form.querySelectorAll('input[name="book"]').forEach(e => e.remove());

                                    // Add JSON data as a single hidden input
                                    const bookInput = document.createElement('input');
                                    bookInput.type = 'hidden';
                                    bookInput.name = 'book';
                                    bookInput.value = JSON.stringify(book);
                                    form.appendChild(bookInput);

                                    // Submit the form
                                    form.submit();
                                });
// Append the result item to the results div
                                resultsDivApi.appendChild(resultItem);
                            });
                        })
                        .catch((error) => {
                            console.error('Error:', error);  // Log any errors
                        });
                }
            })
    </script>
@endsection
