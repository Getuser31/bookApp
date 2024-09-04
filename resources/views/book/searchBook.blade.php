<div class="relative">
<input
    type="search"
    id="GoogleBookSearch"
    placeholder="Find a book..."
    class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
/>
<meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="absolute left-0 right-0 bg-white border border-gray-300 rounded shadow-md max-h-60 overflow-auto mt-1 hidden" id="searchApiResults"></div>
</div>

@vite(['resources/js/searchBook.js'])
@vite(['resources/css/book/searchBook.css'])

