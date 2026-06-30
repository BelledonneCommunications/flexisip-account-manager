@extends('layouts.main')

@if ($admin_view)
    @section('breadcrumb')
        @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
        <li class="breadcrumb-item active" aria-current="page">{{ __('Calls logs') }}</li>
    @endsection
@endif

@section('content')
    @if ($admin_view)
        <header>
            <h1><i class="ph ph-users"></i> {{ $account->identifier }}</h1>
        </header>
        @include('admin.account.parts.tabs')
    @else
        <header>
            <h1><i class="ph ph-phone"></i> {{ __('Calls logs') }}</h1>
        </header>
    @endif

    <div>
        <form class="inline" method="POST"
            @if ($admin_view)
                action="{{ route('admin.account.statistics.edit_call_logs', $account->id) }}"
            @else
                action="{{ route('account.statistics.edit_call_logs') }}"
            @endif
            accept-charset="UTF-8">
            @csrf
            @method('post')

            <input type="hidden" name="page" value="{{  request()->input('page', '') }}">

            <div>
                <input type="date" name="from" value="{{ $request->get('from') }}" onchange="this.form.submit()">
                <label for="from">{{ __('From') }}</label>
            </div>
            <div>
                <input type="date" name="to" value="{{ $request->get('to') }}" onchange="this.form.submit()">
                <label for="to">{{ __('To') }}</label>
            </div>

            @if (!$admin_view)
                <div class="select">
                    <select name="direction" onchange="this.form.submit()">
                        @foreach (getDirections() as $key => $name)
                            <option value="{{ $key }}"
                                @if (request()->input('direction', '') == $key) selected="selected" @endif>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <label for="direction">{{ __('Direction') }}</label>
                </div>
            @endif

            <div class="oppose">
                <a class="btn secondary"
                    @if ($admin_view)
                        href="{{ route('admin.account.statistics.show_call_logs', $account->id) }}"
                    @else
                        href="{{ route('account.statistics.show_call_logs') }}"
                    @endif>{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    @include('parts.call_logs.table', ['calls' => $calls, 'admin_view' => $admin_view])
@endsection
