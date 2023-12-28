@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.dictionary.index', $account) }}">Dictionary</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Dictionary entry deletion</h2>

    <div>
        <p>Are you sure you want to delete the following dictionary entry?</p>
        <p>
            <b>{{ $entry->key }}:</b> {{ $entry->value }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.account.dictionary.destroy', $account) }}" accept-charset="UTF-8">
        @method('delete')
        @csrf
        <input name="key" type="hidden" value="{{ $entry->key }}">
        <div>
            <input class="btn" type="submit" value="Delete">
        </div>

    </form>
@endsection
