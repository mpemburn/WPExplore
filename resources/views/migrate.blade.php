@extends('layouts.app')
@section('content')
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="p-2 mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow rounded">
            <div class="flex items-center">
                <h4>Subsite Migrator:</h4>
            </div>
        </div>
        <div class="row align-items-start mt-4 p-2">
            <section class="col border m-1 p-2">
                <h4>From</h4>
                <label for="database_from">Database:</label>
                <select id="database_from" name="database_from">
                    <option value="">Select</option>
                    @foreach($databases as $label => $dbName)
                        <option value="{{ $dbName }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" id="filter" placeholder="Filter selection"/>
                <button id="migrate_btn" class="btn btn-primary btn-sm" disabled>Migrate</button>
                <img id="loading" class="d-none"
                     src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" alt="" width="24"
                     height="24">
                <div id="max_error" class="text-danger">A maximum of five subsites can be processed at one time.</div>
                <div class="mt-2">
                    <select class="border" name="subsites_from" id="subsites_from" size="20" multiple>
                    </select>
                </div>
            </section>
            <section class="col border m-1 p-2">
                <h4>To</h4>
                <label for="database_to">Database:</label>
                <select id="database_to" name="database_to" class="mb-1">
                    <option value="">Select</option>
                    @foreach($databases as $label => $dbName)
                        <option value="{{ $dbName }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="mt-2">
                    <select class="border" name="subsites_to" id="subsites_to" size="20" multiple disabled>
                    </select>
                </div>
            </section>
        </div>
    </div>
@endsection
