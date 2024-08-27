@extends('index')

@section('content')

    @vite(['resources/css/user/userProfile.css'])

    <div class="message">
        @if (session('status'))
            <div class="text-red-500 alert alert-success">
                {{ session('status') }}
            </div>
        @endif
    </div>
    <div class="outer-container">

        <div class="container">

            <div class="statistics p-4 bg-white rounded-lg shadow-md">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-2">Statistics:</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li>{{$user->books->count()}} Total books belonging</li>
                    <li>{{intval($averageRanking)}}/10 average ranking</li>
                    <li>{{$bookStarted}} Book{{$bookStarted > 1 ? 's' : ''}} started</li>
                    <li>{{$bookNotStarted}} Book{{$bookNotStarted > 1 ? 's' : ''}} not started</li>
                    <li># Book in wishlist</li>
                </ul>
            </div>

            <div class="userData p-4 bg-white rounded-lg shadow-md">
                <form action="{{ route('UpdateUserData') }}" method="post" class="space-y-4">
                    @csrf
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Personal data:</h3>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="name" id="username" value="{{$user->name}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="{{$user->email}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Envoyer
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection
