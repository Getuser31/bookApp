@extends('index')

@section('content')
    <div class="container mx-auto">
        <H1 class="text-2xl">Welcome to Admin</H1>

        <div class="container mx-auto px-4">
            <ul>
                <li><a class="text-blue-600 visited:text-purple-600 ..." href="{{route('admin.genre')}}">Handle Genre</a></li>
                <li><a class="text-blue-600 visited:text-purple-600 ..." href="{{route('admin.author')}}">Handle Author</a></li>
                <li><a class="text-blue-600 visited:text-purple-600 ..." href="{{route('admin.book')}}">Handle Book</a></li>
                <li><a class="text-blue-600 visited:text-purple-600 ..." href="{{route('admin.collection')}}">Handle Collection</a></li>
                <li><a class="text-blue-600 visited:text-purple-600 ..." href="{{route('admin.users')}}">Handle Users</a></li>
            </ul>
        </div>
    </div>
@endsection
