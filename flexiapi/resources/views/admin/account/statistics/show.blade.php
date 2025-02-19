@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Statistics') }}</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">users</i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

<div>
    <form class="inline" method="POST" action="{{ route('admin.account.statistics.edit', $account) }}" accept-charset="UTF-8">
        @csrf
        @method('post')

        <input type="hidden" name="by" value="{{ request()->get('by', 'day') }}">

        <div>
            <input type="date" name="from" value="{{ request()->get('from') }}" onchange="this.form.submit()">
            <label for="from">{{ __('From') }}</label>
        </div>
        <div>
            <input type="date" name="to" value="{{ request()->get('to') }}" onchange="this.form.submit()">
            <label for="to">{{ __('To') }}</label>
        </div>

        <div>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'day'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'day') selected @endif">{{ __('Day') }}</a>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'week'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'week') selected @endif">{{ __('Week') }}</a>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'month'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'month') selected @endif">{{ __('Month') }}</a>
            <a href="{{ route('admin.account.statistics.show', ['account' => $account, 'by' => 'year'] + request()->only(['from', 'to', 'domain'])) }}"
                class="chip @if (request()->get('by', 'day') == 'year') selected @endif">{{ __('Year') }}</a>
        </div>
    </form>
</div>

<h2><i class="ph">envelope</i> Messages from the account</h2>

{!! $messagesFromGraph !!}

<h2><i class="ph">envelope</i> Messages to the account</h2>

{!! $messagesToGraph !!}

<h2><i class="ph">phone</i> Calls from the account</h2>

{!! $callsFromGraph !!}

<h2><i class="ph">phone</i> Calls to the account</h2>

{!! $callsToGraph !!}

@endsection