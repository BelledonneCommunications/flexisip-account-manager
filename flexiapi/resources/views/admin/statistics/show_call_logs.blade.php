@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="ph ph-chart-donut"></i> {{ __('Statistics') }}</h1>
    </header>

    @include('admin.statistics.parts.tabs')

    <div>
        <form class="inline" method="POST" action="{{ route('admin.statistics.edit_call_logs') }}" accept-charset="UTF-8">
            @csrf
            @method('post')

            <div>
                <input type="date" name="from" value="{{ $request->get('from') }}" onchange="this.form.submit()">
                <label for="from">{{ __('From') }}</label>
            </div>
            <div>
                <input type="date" name="to" value="{{ $request->get('to') }}" onchange="this.form.submit()">
                <label for="to">{{ __('To') }}</label>
            </div>

            <div class="large on_desktop"></div>

            @include('admin.account.parts.forms.select_domain')

            <div class="select">
                <select name="contacts_list" onchange="this.form.submit()">
                    <option value="">
                        {{ __('Select a contacts list') }}
                    </option>
                    @foreach ($contacts_lists as $key => $name)
                        <option value="{{ $key }}"
                            @if (request()->get('contacts_list', '') == $key) selected="selected" @endif>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <label for="contacts_list">{{ __('Contacts Lists') }}</label>
            </div>

            <div class="on_desktop"></div>

            <div class="oppose">
                <a class="btn secondary" href="{{ route('admin.statistics.show_call_logs') }}">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    @include('parts.call_logs.table', ['calls' => $calls])
@endsection
