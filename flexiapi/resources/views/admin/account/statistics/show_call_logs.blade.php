@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">Call Logs</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-symbols-outlined">people</i> {{ $account->identifier }}</h1>
    </header>

    @include('admin.account.parts.tabs')

    <div>
        <form class="inline" method="POST" action="{{ route('admin.account.statistics.edit_call_logs', $account->id) }}" accept-charset="UTF-8">
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

            <div class="oppose">
                <a class="btn btn-secondary" href="{{ route('admin.account.statistics.show_call_logs', $account->id) }}">Reset</a>
            </div>
        </form>
    </div>

    @include('parts.call_logs.table', ['calls' => $calls])
@endsection
