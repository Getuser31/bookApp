@extends('index')

@section('content')
    <ul class="bg-white shadow rounded-lg p-6 my-4 max-w-lg">
        <li class="flex justify-between border-b border-gray-200 py-2">
            <span class="font-semibold">Username:</span>
            <span>{{$user->name}}</span>
        </li>
        @if(session('admin'))
        <li class="flex justify-between border-b border-gray-200 py-2">
            <span class="font-semibold">ID:</span>
            <span>{{$user->id}}</span>
        </li>
        @endif
        <li class="flex justify-between border-b border-gray-200 py-2">
            <span class="font-semibold">Email:</span>
            <span>{{$user->email}}</span>
        </li>
        <li class="flex justify-between py-2">
            <span class="font-semibold">Role:</span>
            <span>{{$user->role->name}}</span>
        </li>
    </ul>

    @if(session('admin'))
       @include('User.deleteUserForm')
        <a href="{{route('updateAccount', ['user' => $user])}}" class="mt-10 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-red-blue">
            Update User
        </a>
    @endif

@endsection
