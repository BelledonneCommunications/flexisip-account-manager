@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.index') }}">Accounts</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Import</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-icons-outlined">people</i> Import accounts</h1>
        <a href="{{ route('admin.account.index') }}" class="btn btn-secondary oppose">Cancel</a>
        <input form="import" class="btn" type="submit" value="Next">
    </header>

    <div>
        <ol class="steps" style="margin: 6rem 0;">
            <li class="active">Select data file</li>
            <li>Import data</li>
        </ol>

        <p>Use this existing (.csv) template or create your own csv file.</p>

        <p>
            This file MUST be in csv format and contain at least the following information:
        </p>
        <ol>
            <li>The first line contains the labels</li>
            <li>Username* </li>
            <li>Password* (6 characters minimum)</li>
            <li>Role* (admin or user)</li>
            <li>Statuts* (active, inactive)</li>
            <li>Phone number, must start with a + if set</li>
        </ol>

        <hr />

        <form id="import" method="POST" action="{{ route('admin.account.import.store') }}" accept-charset="UTF-8" enctype="multipart/form-data">
            @csrf
            <div>
                <input name="csv" type="file" accept=".csv">
                @include('parts.errors', ['name' => 'csv'])
                <label for="csv">CSV file to import</label>
            </div>
            <div class="on_desktop"></div>

            <div class="select">
                <select name="domain">
                    @foreach ($domains as $domain)
                        <option value="{{ $domain }}">{{ $domain }}</option>
                    @endforeach
                </select>
                <label for="domain">Domain used for the import</label>
            </div>
        </form>
    </div>
@endsection
