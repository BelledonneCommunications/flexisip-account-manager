@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">{{ __('Dictionary') }}</li>
@endsection

@section('content')
    <h1><i class="ph ph-trash"></i> {{ __('Delete') }}</h1>

    <div>
        <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
        <p>
            <b>{{ $entry->key }}:</b> {{ $entry->value }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.account.dictionary.destroy', $account) }}" accept-charset="UTF-8">
        @method('delete')
        @csrf
        <input name="key" type="hidden" value="{{ $entry->key }}">
        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>

    </form>
@endsection
