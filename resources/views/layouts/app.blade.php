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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="fixed-top mb-4 pt-4 pb-2 ps-3 bg-white shadow" style="height: 6rem;">
        <h4>WP Explore</h4>
        <a class="nav-item" href="{{route('dashboard')}}">Dashboard</a>
        <a class="nav-item" href="{{route('index')}}">Searches</a>
        <a class="nav-item" href="{{route('csv')}}">CSV Downloader</a>
        <a class="nav-item" href="{{route('migrate')}}">Migrator</a>
        <div style="top: -4rem; position: relative; text-align: right; margin-right: 2rem;">
            <script>
                $(document).ready(function ($) {
                    let tz = zone = new Date().toLocaleTimeString('en-us',{timeZoneName:'short'}).split(' ')[2];
                    $('#tz').html(tz);
                    $('#udt').on('keyup', function () {
                        let udt = $(this).val();
                        let inDate = new Date(udt + ' GMT');
                        let result = isNaN(inDate) ? '' : inDate.toLocaleDateString('en-us', {
                            year:"numeric",
                            month:"short",
                            day:"numeric",
                            hour: '2-digit',
                            minute:'2-digit'
                        });
                        $('#to_eastern').html(result);
                    });
                });
            </script>
            UDT to <span id="tz"></span>:&nbsp;<input type="text" id="udt"/>&nbsp;
            <div id="to_eastern" style="margin-right: 1rem;"></div>

        </div>
    </nav>
    <div class="content position-relative">
        <?php flash('My message'); ?>
        @yield('content')
    </div>
</body>
</html>
