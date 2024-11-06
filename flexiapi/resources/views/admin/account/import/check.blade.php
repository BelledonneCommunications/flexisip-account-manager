@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item active" aria-current="page">Import</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">users</i> Import accounts</h1>
        <a href="{{ route('admin.account.index') }}" class="btn btn-secondary oppose">Cancel</a>

        <a href="#" onclick="history.back()" class="btn btn-secondary">Previous</a>
        <form name="handle" method="POST" action="{{ route('admin.account.import.handle') }}" accept-charset="UTF-8"
            enctype="multipart/form-data">
            @csrf
            <input name="file_path" type="hidden" value="{{ $filePath }}">
            <input name="domain" type="hidden" value="{{ $domain }}">
            <a type="submit"
                class="btn @if ($errors->isNotEmpty()) disabled @endif" onclick="document.querySelector('form[name=handle]').submit()">
                <i class="ph">download-simple</i>
                Import
            </a>
        </form>
    </header>

    <div>
        <ol class="steps" style="margin: 6rem 0;">
            <li>Select data file</li>
            <li class="active">Import data</li>
        </ol>

        <h3>{{ $linesCount }} accounts will be imported for the {{ $domain }} domain</h3>

        @if ($errors->isNotEmpty())
            <hr />

            <h3>Errors</h3>

            @foreach ($errors as $title => $body)
                <p><b>{{ $title }}</b> {{ $body }}</p>
            @endforeach
        @endif
    </div>
@endsection
