@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
<li class="breadcrumb-item active" aria-current="page">Dictionary</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">users</i> {{ $account->identifier }}</h1>
    <a href="{{ route('admin.account.edit', $account->id) }}" class="btn btn-secondary oppose">Cancel</a>
    <a class="btn" href="{{ route('admin.account.dictionary.create', $account) }}">
        <i class="ph">plus</i>
        New Entry
    </a>
</header>

@include('admin.account.parts.tabs')

<table>
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @if ($account->dictionaryEntries->isEmpty())
            <tr class="empty">
                <td colspan="3">No entries</td>
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
                        Edit
                    </a>
                    <a type="button"
                       class="btn btn-secondary"
                       href="{{ route('admin.account.dictionary.delete', [$account, $dictionaryEntry->key]) }}">
                        Delete
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@endsection