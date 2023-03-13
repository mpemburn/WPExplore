<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Log Parser</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/log_parser.css') }}">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
    <script src="{{ asset('js/log_parser.js') }}" defer></script>

</head>
    <div data-log="{{ $logPrefix }}" class="log">Log: {{ $logPrefix }}</div>

    @foreach($data as $line)
        {!! $line !!}
    @endforeach
</html>
