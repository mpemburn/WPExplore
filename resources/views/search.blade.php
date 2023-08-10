@extends('layouts.app')
@section('content')
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
                        <button id="search_btn" class="btn btn-primary btn-sm" disabled>Search</button>
                        <img id="loading" class="d-none"
                             src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" alt="" width="24"
                             height="24">
                    </form>
                    <div id="found"></div>
                    <div id="results"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
