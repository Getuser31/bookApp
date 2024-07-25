@extends('index')

@section('content')
    <ul class="list-disc pl-5">
        <li class="mb-2">
            <strong class="font-semibold">User Name:</strong> <span class="text-gray-700">{{$user->name}}</span>
        </li>
        <li class="mb-2">
            <strong class="font-semibold">ID:</strong> <span class="text-gray-700">{{$user->id}}</span>
        </li>
        <li class="mb-2">
            <strong class="font-semibold">Email:</strong> <span class="text-gray-700">{{$user->email}}</span>
        </li>
        <li class="mb-2">
            <strong class="font-semibold">Role:</strong> <span class="text-gray-700">{{$user->role->name}}</span>
        </li>
    </ul>

   @include('User.deleteUserForm')

@endsection
