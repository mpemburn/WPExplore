<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>WP Explore</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
    <script
        src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
        crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/css/newyou.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="fixed-top mb-4 pt-4 pb-2 ps-3 bg-white shadow">
        <h4>WP Explore</h4>
        <a class="nav-item" href="{{route('dashboard')}}">Dashboard</a>
        <a class="nav-item" href="{{route('search')}}">Searches</a>
        <a class="nav-item" href="{{route('csv')}}">CSV Downloader</a>
        <a class="nav-item" href="{{route('migrate')}}">Migrator</a>
    </nav>
    <div class="content position-relative">
        @yield('content')
    </div>
</body>
</html>
