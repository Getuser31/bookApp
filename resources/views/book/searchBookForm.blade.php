<h1 class="text-4xl text-center font-bold text-blue-500"> Add book to your library</h1>
<section>
    <h2 class="text-2xl text-left font-bold text-green-600">Search for a book</h2>
    <input
        class="shadow appearance-none border rounded w-1/3 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        type="text" placeholder="Search for a book" id="bookSearchInput">
    <div id="bookSearchResults"></div>
</section>
<script>
    const BOOK_SEARCH_URL_BASE = "{{route('book.searchBook')}}?search=";

    const bookSearchInput = document.getElementById('bookSearchInput');
    const bookSearchResults = document.getElementById('bookSearchResults');

    bookSearchInput.addEventListener('input', handleBookSearchInput);

    function handleBookSearchInput(event) {
        const searchQuery = event.target.value;
        if (searchQuery.length > 3) {
            fetchBooks(searchQuery);
        }
    }

    function fetchBooks(query) {
        fetch(BOOK_SEARCH_URL_BASE + query, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                renderSearchResults(data.books);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function renderSearchResults(books) {
        bookSearchResults.innerHTML = '';
        books.forEach(book => {
            const resultItem = document.createElement('div');
            resultItem.textContent = book.title;
            resultItem.classList.add('search-result-item');
            resultItem.addEventListener('click', () => {
                window.location.href = "/book/" + book.id;
            });
            bookSearchResults.appendChild(resultItem);
        });
    }
</script>

