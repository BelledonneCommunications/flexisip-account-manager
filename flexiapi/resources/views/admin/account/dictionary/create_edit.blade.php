@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Dictionary') }}</li>
@endsection

@section('content')
    <h1><i class="ph @if ($entry->id)ph-pencil @else ph-plus @endif"></i> {{ __('Dictionary') }}</h1>

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
