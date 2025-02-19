@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
<li class="breadcrumb-item active" aria-current="page">{{ __('Dictionary') }}</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">users</i> {{ $account->identifier }}</h1>
    <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-secondary oppose">{{ __('Cancel') }}</a>
    <a class="btn" href="{{ route('admin.account.dictionary.create', $account) }}">
        <i class="ph">plus</i>
        {{ __('Add') }}
    </a>
</header>

@include('admin.account.parts.tabs')

<table>
    <thead>
        <tr>
            <th>{{ __('Key') }}</th>
            <th>{{ __('Value') }}</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @if ($account->dictionaryEntries->isEmpty())
            <tr class="empty">
                <td colspan="3">{{ __('Empty') }}</td>
            </tr>
        @endif
        @foreach ($account->dictionaryEntries as $dictionaryEntry)
            <tr>
                <td>{{ $dictionaryEntry->key }}</td>
                <td>{{ $dictionaryEntry->value }}</td>
                <td>
                    <a type="button"
                       class="btn"
                       href="{{ route('admin.account.dictionary.edit', [$account, $dictionaryEntry->key]) }}">
                        {{ __('Edit') }}
                    </a>
                    <a type="button"
                       class="btn btn-secondary"
                       href="{{ route('admin.account.dictionary.delete', [$account, $dictionaryEntry->key]) }}">
                        {{ __('Delete') }}
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection