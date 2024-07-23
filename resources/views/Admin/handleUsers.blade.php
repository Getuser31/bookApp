@extends('index')


@section('content')
    <h1 class="text-4xl font-bold text-gray-900 text-center">User Management</h1>

    <h3 class="text-2xl text-blue-500">Seek For A User</h3>
    <form method="post" action="{{route('admin.seekUser')}}">
        @csrf
    <input
        name="userName"
        type="search"
        placeholder="Username"
        class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-blue-500 focus:outline-none"
    />
    </form>

@endsection
