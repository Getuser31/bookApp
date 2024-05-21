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
            <a href="#" class="text-white">Home</a>
            <a href="#" class="text-white">About</a>
            <a href="#" class="text-white">Services</a>
            <a href="#" class="text-white">Contact</a>
        </div>
    </div>
    @yield('content')

<!-- Scripts -->
@vite(['resources/js/app.js'])
</body>
</html>
