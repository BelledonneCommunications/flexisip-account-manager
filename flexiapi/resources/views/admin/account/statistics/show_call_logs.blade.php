@extends('layouts.main')

@if ($adminView)
    @section('breadcrumb')
        @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
        <li class="breadcrumb-item active" aria-current="page">{{ __('Calls logs') }}</li>
    @endsection
@endif

@section('content')
    @if ($adminView)
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
            @if ($adminView)
                action="{{ route('admin.account.statistics.edit_call_logs', $account->id) }}"
            @else
                action="{{ route('account.statistics.edit_call_logs') }}"
            @endif
            accept-charset="UTF-8">
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

            <div class="oppose">
                <a class="btn secondary"
                    @if ($adminView)
                        href="{{ route('admin.account.statistics.show_call_logs', $account->id) }}"
                    @else
                        href="{{ route('account.statistics.show_call_logs') }}"
                    @endif>{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    @include('parts.call_logs.table', ['calls' => $calls])
@endsection
