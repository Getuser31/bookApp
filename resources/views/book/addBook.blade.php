@extends('index')

@section('content')
    <h1 class="text-4xl text-center font-bold text-blue-500"> Add book to your library</h1>

    <section>
        <h2 class="text-2xl text-left font-bold text-green-600">Search for a book in local Database</h2>

        <input
            class="shadow appearance-none border rounded w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            type="text" placeholder="Search for a book" id="bookSearch">

        <div id="searchResults"></div>


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
                                resultItem.textContent = item.volumeInfo.title; // Assuming item object has a "title" property
                                resultItem.classList.add('search-result-item'); // Add a class for CSS styling

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
                                        thumbnail: item.volumeInfo.imageLinks.thumbnail,
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

    <script>
        let inputField = document.getElementById('bookSearch');
        let resultsDiv = document.getElementById('searchResults'); // Select the results div

        inputField.addEventListener('input',

            (event) => {
                const value = event.target.value;
                if (value.length > 3) {
                    // The URL endpoint and the string you want to send
                    let url = "{{route('book.searchBook')}}" + "?search=" + value


                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            // 'Authorization': `Bearer ${YourToken}`,  // Uncomment this line if you need to send a Bearer token for authorization
                        },
                    })
                        .then(response => response.json())  // Parse the returned JSON
                        .then(data => {
                            console.log(data);  // Log the data received from the server.

                            // Clear any previous results
                            resultsDiv.innerHTML = '';
                            const books = data.books;

                            // Loop through each item in returned data and append to search results div
                            books.forEach(item => {
                                let resultItem = document.createElement('div');
                                resultItem.textContent = item.title; // Assuming item object has a "title" property
                                resultItem.classList.add('search-result-item'); // Add a class for CSS styling

                                // Make result item clickable and going to book detail page
                                resultItem.addEventListener('click', function () {
                                    window.location.href = "/book/" + item.id;  // Assuming item object has an "id" property
                                });

                                // Append the result item to the results div
                                resultsDiv.appendChild(resultItem);
                            });
                        })
                        .catch((error) => {
                            console.error('Error:', error);  // Log any errors
                        });
                }
            })
    </script>
@endsection
