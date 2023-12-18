@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.edit', $account->id) }}">{{ $account->identifier }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Types
    </li>
@endsection

@section('content')
    <h2>Add a Type to the Account</h2>

    @if ($account_types->count() == 0)
        <p>
            No Account Type to add
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
                <label for="account_type_id">Account Type</label>
            </div>
            <div>
                <input class="btn btn-success" type="submit" value="Add">
            </div>
        </form>
    @endif
@endsection
