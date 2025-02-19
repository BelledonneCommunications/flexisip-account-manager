@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Import') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">users</i> {{ __('Import') }}</h1>
        <a href="{{ route('admin.account.index') }}" class="btn btn-secondary oppose">{{ __('Cancel') }}</a>
        <input form="import" class="btn" type="submit" value="{{ __('Next') }}">
    </header>

    <div>
        <ol class="steps" style="margin: 6rem 0;">
            <li class="active">{{ __('Select a file') }}</li>
            <li>{{ __('Import') }}</li>
        </ol>

        <p>{{ __('The file must be in CSV following this template') }}: <a href="{{ route('account.home') }}/accounts_example.csv">example_template.csv</a></p>
        <ol>
            <li>{{ __('The first line contains the labels') }}</li>
            <li>{{ __('Username') }}* </li>
            <li>{{ __('Password') }}* (6 characters minimum)</li>
            <li>{{ __('Role') }}* (admin or user)</li>
            <li>{{ __('Status') }}* (active, inactive)</li>
            <li>{{ __('Phone number') }}</li>
            <li>{{ __('Email') }}</li>
        </ol>

        <hr />

        <form id="import" method="POST" action="{{ route('admin.account.import.store') }}" accept-charset="UTF-8" enctype="multipart/form-data">
            @csrf
            <div>
                <input name="csv" type="file" accept=".csv">
                @include('parts.errors', ['name' => 'csv'])
                <label for="csv">{{ __('Select a file') }}</label>
            </div>
            <div class="on_desktop"></div>

            <div class="select">
                <select name="domain">
                    @foreach ($domains as $domain)
                        <option value="{{ $domain }}">{{ $domain }}</option>
                    @endforeach
                </select>
                <label for="domain">{{ __('Domain') }}</label>
            </div>
        </form>
    </div>
@endsection
