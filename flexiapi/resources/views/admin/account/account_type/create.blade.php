@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('Types') }}
    </li>
@endsection

@section('content')
    <h2>{{ __('Add') }}</h2>

    @if ($account_types->count() == 0)
        <p>
            {{ __('Empty') }}
        </p>
    @else
        <form method="POST" action="{{ route('admin.account.account_type.store', $account->id) }}" accept-charset="UTF-8">
            @csrf
            <div class="select">
                <select name="account_type_id">
                    @foreach ($account_types as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <label for="account_type_id">{{ __('Types') }}</label>
            </div>
            <div>
                <input class="btn btn-success" type="submit" value="{{ __('Add') }}">
            </div>
        </form>
    @endif
@endsection
