@extends('index')

@section('content')

    @vite(['resources/css/user/userProfile.css'])

    <div class="outer-container">
        <div class="container">

            <div class="statistics">
                <p>Statistics :</p>
                <p>{{$user->books->count()}} Total books belonging</p>
                <p># Books started</p>
                <p># Book not started</p>
                <p># Book in wishlist</p>
            </div>

            <div class="userData">
                <p>Personal data:</p>
                <p>username</p>
                <p>email</p>
            </div>
        </div>
    </div>

@endsection
