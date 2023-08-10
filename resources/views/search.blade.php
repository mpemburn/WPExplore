<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Laravel</title>

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
    @vite('resources/js/app.js')
</head>
<body class="antialiased bg-gray-100">
<div class="m-4">

    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
            <h2>WP Explorer</h2>
        </div>

        <div class="p-2 mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow rounded">
            <div class="">
                <div class="p-6">
                    <div class="flex items-center">
                        <h4>Searches:</h4>
                    </div>
                    <form id="search_form">
                        <label for="type">Search type:
                            <select name="type">
                                <option value="posts">In Posts</option>
                                <option value="postmeta">In Postmeta</option>
                                <option value="options">In Options</option>
                                <option value="option_name">Option Name</option>
                                <option value="shortcodes">Shortcodes</option>
                            </select>
                        </label>
                        <label for="database">Database:
                            <select name="database">
                                @foreach($databases as $label => $dbName)
                                    <option value="{{ $dbName }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <input type="text" name="text" placeholder="Enter search term">
                        <button id="search_btn" class="btn btn-primary btn-sm" style="position: relative; top: -2px;" disabled>Search</button>
                        <img id="loading" class="d-none" src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" alt="" width="24" height="24">                </div>
                    </form>
                <div id="found"></div>
                <div id="results"></div>
            </div>
        </div>

    </div>
</div>
</body>
</html>
