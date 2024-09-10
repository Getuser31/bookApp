<div class="relative">
<input
    type="search"
    id="GoogleBookSearch"
    placeholder="Find a book..."
    class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
/>
<meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="absolute left-0 right-0 bg-white border border-gray-300 rounded shadow-md max-h-60 overflow-auto mt-1 hidden" id="searchApiResults">
        <div class="search-result-item">
            <div class="thumbnail">
                <img src="thumbnail.png" alt="Thumbnail">
            </div>
            <div class="content">
                <!-- Your other content here -->
                <p>Some result content</p>
            </div>
            <button class="more-results-button">More results</button>
        </div>
    </div></div>

@vite(['resources/css/book/searchBook.css'])



