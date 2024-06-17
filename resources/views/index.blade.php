<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- Styles -->
    @vite(['resources/css/app.css'])
</head>
<body>

<div class="bg-blue-500 p-4 flex items-center justify-between">
    <div class="text-white text-lg font-bold">Logo</div>
    <div class="space-x-4">
        <a href="{{route('book.index')}}" class="text-white">Home</a>
        @if(session('admin'))
            <a href="{{route('admin.index')}}" class="text-white">Admin</a>
        @endif
        @if(Auth::check())
            <a href="{{route('book.library')}}" class="text-white">Library</a>
            <a href="{{route('logout')}}" class="text-white">Logout</a>
        @endif
    </div>
</div>
@yield('content')

<!-- Scripts -->
@vite(['resources/js/app.js'])
</body>
</html>
