@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Dictionary') }}</li>
@endsection

@section('content')
    @if ($entry->id)
        <h2>{{ __('Edit') }}</h2>
    @else
        <h2>{{ __('Create') }}</h2>
    @endif

    <form method="POST"
        action="{{ $entry->id ? route('admin.account.dictionary.update', [$entry->account, $entry]) : route('admin.account.dictionary.store', $account) }}"
        accept-charset="UTF-8">
        @method($entry->id ? 'put' : 'post')
        @csrf
        <div>
            <input type="text" name="key" value="{{ $entry->key }}" placeholder="key" @if ($entry->id)disabled @endif>
            <label for="key">{{ __('Key') }}</label>
            @include('parts.errors', ['name' => 'key'])
        </div>
        <div>
            <input type="text" name="value" value="{{ $entry->value }}" placeholder="value">
            <label for="value">{{ __('Value') }}</label>
            @include('parts.errors', ['name' => 'value'])
        </div>
        <div>
            <input class="btn btn-success" type="submit" value="{{ $entry->id ? __('Update') : __('Create') }}">
        </div>
    </form>
@endsection
