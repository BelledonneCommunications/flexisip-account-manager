@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Statistics</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-symbols-outlined">analytics</i> Statistics</h1>
    </header>

    @include('admin.statistics.parts.tabs')

    <div>
        <form class="inline" method="POST" action="{{ route('admin.statistics.edit_call_logs') }}" accept-charset="UTF-8">
            @csrf
            @method('post')

            <div>
                <input type="date" name="from" value="{{ $request->get('from') }}" onchange="this.form.submit()">
                <label for="from">From</label>
            </div>
            <div>
                <input type="date" name="to" value="{{ $request->get('to') }}" onchange="this.form.submit()">
                <label for="to">To</label>
            </div>

            <div class="large on_desktop"></div>

            @include('admin.account.parts.forms.select_domain')

            <div class="select">
                <select name="contacts_list" onchange="this.form.submit()">
                    <option value="">
                        Select a contacts list
                    </option>
                    @foreach ($contacts_lists as $key => $name)
                        <option value="{{ $key }}"
                            @if (request()->get('contacts_list', '') == $key) selected="selected" @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <label for="contacts_list">Contacts list</label>
            </div>

            <div class="on_desktop"></div>

            <div class="oppose">
                <a class="btn btn-secondary" href="{{ route('admin.statistics.show_call_logs') }}">Reset</a>
            </div>
        </form>
    </div>

    @include('parts.call_logs.table', ['calls' => $calls])
@endsection
