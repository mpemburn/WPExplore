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
                <select id="database_from" name="database_from" required>
                    <option value="">Select</option>
                    @foreach($databases as $label => $dbName)
                        <option value="{{ $dbName }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button id="migrate_btn" class="btn btn-primary btn-sm">Migrate</button>
                <div class="mt-2">
                    <select class="d-none" name="subsites_from" id="subsites_from" size="20" multiple>
                    </select>
                </div>
            </section>
            <section class="col border m-1 p-2">
                <h4>To</h4>
                <label for="database_to">Database:</label>
                <select id="database_to" name="database_to" required>
                    <option value="">Select</option>
                    @foreach($databases as $label => $dbName)
                        <option value="{{ $dbName }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="mt-2">
                    <select class="d-none" name="subsites_to" id="subsites_to" size="20" multiple>
                    </select>
                </div>
            </section>
        </div>
    </div>
@endsection
