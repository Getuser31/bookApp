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
                <!-- Button to open modal -->
                <button
                    class="bg-indigo-600 hover:bg-indigo-700 border border-transparent text-white font-bold py-2 px-4 rounded-md shadow-sm"
                    onclick="toggleModal()"
                >
                    Update Password
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Background -->
    <div id="modal" class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <!-- Modal Container -->
        <div class="bg-white w-1/2 rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center border-b pb-3">
                <p class="text-2xl font-semibold">Password Update</p>
                <button class="text-black close-modal" onclick="toggleModal()">&times;</button>
            </div>

            <div class="my-4">
                <form method="POST" action="{{ route('updatePassword') }}">
                    @csrf <!-- Include CSRF token for Laravel forms -->

                    <!-- Display Validation Errors -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Whoops!</strong>
                            <span class="block sm:inline">There were some problems with your input.</span>
                            <ul class="mt-3 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Repeat Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                    <div class="flex justify-end pt-2 mt-4">
                        <button
                            type="button"
                            class="focus:outline-none px-4 bg-gray-400 p-3 rounded-lg text-black hover:bg-gray-300 mr-2"
                            onclick="toggleModal()"
                        >
                            Close
                        </button>
                        <button
                            type="submit"
                            class="focus:outline-none px-4 bg-blue-500 p-3 rounded-lg text-white hover:bg-indigo-400"
                        >
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleModal() {
            document.getElementById('modal').classList.toggle('hidden');
        }
    </script>

@endsection
