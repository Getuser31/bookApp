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
                </tr>
                </thead>
                <tbody>
                @foreach($books as $book)
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ ($book->title)}}</td>
                        <td class="px-6 py-4">{{ ($book->date_of_publication)}}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->author->name }}</td>
                        <td class="px-6 py-4 w-1/4">{{\Illuminate\Support\Str::limit($book->description, 200)}}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">{{ $book->genre->name }}</td>
                        <td class="px-6 py-4">{{ $book->collection->name ?? '' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-800">%</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Render pagination links -->
    {{ $books->links('vendor.pagination.tailwind') }}

    <div>
        <a href="#" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add A book</a>
    </div>

@endsection
