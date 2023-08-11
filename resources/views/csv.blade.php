@extends('layouts.app')
@section('content')
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="p-2 mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow rounded">
            <div class="">
                <div class="p-6">
                    <div class="flex items-center">
                        <h4>Searches:</h4>
                    </div>
                    <form id="download_form">
                        <label for="csv_type">CSV Type:</label>
                        <select id="csv_type" name="csv_type">
                            <option value="">Select</option>
                            @foreach($csvTypes as $label => $method)
                                <option value="{{ $method }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <label for="database">Database:</label>
                        <select id="database" name="database">
                            <option value="">Select</option>
                            @foreach($databases as $label => $dbName)
                                <option value="{{ $dbName }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <span id="date_range">
                            <label for="start_date">Starting:</label>
                            <input type="date" name="start_date">

                            <label for="end_date">Ending:</label>
                            <input type="date" name="end_date" value="08/10/2023">

                        </span>
                        <input type="hidden" name="today_date" value="{{ $todayDate }}">
                        <button id="download_btn" class="btn btn-primary btn-sm">Download</button>
                    </form>
                    <div id="found"></div>
                    <div id="results"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
