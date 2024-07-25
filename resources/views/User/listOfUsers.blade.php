@extends('index')

@section('content')

    <div class="bg-gray-100 text-gray-800">
        <div class="container mx-auto p-4">
            <h1 class="text-3xl font-bold mb-4">Users List</h1>
            <ul class="bg-white rounded-lg shadow-lg p-4">
                @foreach ($users as $user)
                    <li class="border-b border-gray-200 py-2">
                        <a class="text-blue-500 hover:text-blue-700 font-semibold" href="{{route('checkUser', ['id' => $user->id])}}"> {{ $user->name }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4">
                {{ $users->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
@endsection
