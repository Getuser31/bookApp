@extends('index')

@section('content')

    <div class="container">
        <form method="post" action="{{route('admin.book.create')}}" class="max-w-sm mx-auto">
            @csrf
            <label for="title" class="block mb-2 text-sm font-medium text-gray-900 ">Title</label>
            <input id="title" type="text" name="title" value="" class="block w-4 p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500">

            <label for="DateOfPublication" class="block mb-2 text-sm font-medium text-gray-900 ">Date Of Publication</label>
            <input id="DateOfPublication" type="text" name="DateOfPublication" value="" class="block w-4 p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500">

            <label for="description" class="block mb-2 text-sm font-medium text-gray-900 ">description</label>
            <input id="description" type="text" name="description" value="" class="block w-2/3 p-4 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500">


        </form>
    </div>

@endsection
