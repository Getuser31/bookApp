@extends('index')


@section('content')
    <h1 class="text-4xl font-bold text-gray-900 text-center">User Management</h1>

    <h3 class="text-2xl text-blue-500">Create User</h3>

   @include('User.userForm')

    <div class="mb-4 mt-10">
        <h3 class="text-2xl text-blue-500">Seek For A User</h3>
        <form method="post" action="{{route('admin.seekUser')}}">
            @csrf
            <input
                name="userName"
                type="search"
                placeholder="Username or Email"
                class="border border-gray-300 rounded-lg py-2 px-4 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            <button
                type="submit"
                class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Search
            </button>
        </form>
        @if (session('error'))
            <div class="alert alert-danger text-red-500">
                {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-danger text-green-500">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="mb-4 mt-10">
        <a href="{{route('admin.user.list')}}"  class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            List Of Users
        </a>

    </div>
@endsection
