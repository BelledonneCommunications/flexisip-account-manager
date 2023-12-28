@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.dictionary.index', $account) }}">Dictionary</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">@if ($entry->id)Edit @else Create @endif</li>
@endsection

@section('content')
    @if ($entry->id)
        <h2>Edit an Entry</h2>
    @else
        <h2>Create an Entry</h2>
    @endif

    <form method="POST"
        action="{{ $entry->id ? route('admin.account.dictionary.update', [$entry->account, $entry]) : route('admin.account.dictionary.store', $account) }}"
        accept-charset="UTF-8">
        @method($entry->id ? 'put' : 'post')
        @csrf
        <div>
            <input type="text" name="key" value="{{ $entry->key }}" placeholder="key" @if ($entry->id)disabled @endif>
            <label for="key">Key</label>
            @include('parts.errors', ['name' => 'key'])
        </div>
        <div>
            <input type="text" name="value" value="{{ $entry->value }}" placeholder="value">
            <label for="value">Value</label>
            @include('parts.errors', ['name' => 'value'])
        </div>
        <div>
            <input class="btn btn-success" type="submit" value="{{ $entry->id ? 'Update' : 'Create' }}">
        </div>
    </form>
@endsection
