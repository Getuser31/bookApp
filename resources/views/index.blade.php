<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <!-- Styles -->
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>

<div class="bg-blue-500 p-4 flex items-center justify-between">
    <div class="text-white text-lg font-bold">Logo</div>
    <div class="space-x-4 flex items-center">
        <a href="{{ route('book.index') }}" class="text-white">Home</a>
        @if(session('admin'))
            <a href="{{ route('admin.index') }}" class="text-white">Admin</a>
        @endif
        @if(Auth::check())
            <a href="{{route('userProfile')}}" class="text-white">Profile</a>
            <a href="{{ route('book.library') }}" class="text-white">Library</a>
            <span class="logout flex items-center">
                <a href="{{ route('logout') }}" class="text-white flex items-center space-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                    </svg>
                    <span>Logout</span>
                </a>
            </span>
        @endif
    </div>
</div>
@yield('content')

<!-- Scripts -->
@vite(['resources/js/app.js'])
</body>
</html>
