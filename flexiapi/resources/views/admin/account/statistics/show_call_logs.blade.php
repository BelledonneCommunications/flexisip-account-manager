@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item active" aria-current="page">Call Logs</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-icons-outlined">list</i> Call Logs</h1>
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