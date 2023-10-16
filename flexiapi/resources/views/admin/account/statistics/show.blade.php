@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.index') }}">Accounts</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('admin.account.edit', $account) }}">{{ $account->identifier }}</a>
</li>
<li class="breadcrumb-item active" aria-current="page">Statistics</li>
@endsection

@section('content')

<header>
    <h1><i class="material-icons-outlined">people</i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

<div>
    <form class="inline" method="POST" action="{{ route('admin.account.statistics.edit', $account) }}" accept-charset="UTF-8">
        @csrf
        @method('post')

        <input type="hidden" name="by" value="{{ request()->get('by', 'day') }}">

        <div>
            <input type="date" name="from" value="{{ request()->get('from') }}" onchange="this.form.submit()">
            <label for="from">From</label>
        </div>
        <div>
            <input type="date" name="to" value="{{ request()->get('to') }}" onchange="this.form.submit()">
            <label for="to">To</label>
        </div>

        <div>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'day'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'day') selected @endif">Day</a>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'week'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'week') selected @endif">Week</a>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'month'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'month') selected @endif">Month</a>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'year'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'year') selected @endif">Year</a>
        </div>
    </form>
</div>

<h2><i class="material-icons-outlined">message</i> Messages from the account</h2>

{!! $messagesFromGraph !!}

<h2><i class="material-icons-outlined">message</i> Messages to the account</h2>

{!! $messagesToGraph !!}

<h2><i class="material-icons-outlined">call</i> Calls from the account</h2>

{!! $callsFromGraph !!}

<h2><i class="material-icons-outlined">call</i> Calls to the account</h2>

{!! $callsToGraph !!}

@endsection