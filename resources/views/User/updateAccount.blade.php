@extends('index')


@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-xl font-bold mb-6">Update User</h1>

        <!-- Display Success Message -->
        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                {{ session('status') }}
            </div>
    @endif
    </div>
    @include('User.userForm')

@endsection
