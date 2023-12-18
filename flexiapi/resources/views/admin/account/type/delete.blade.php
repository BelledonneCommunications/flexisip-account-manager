@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.type.index') }}">Types</a>
    </li>
    <li class="breadcrumb-item">
        {{ $type->key }}
    </li>
    <li class="breadcrumb-item active" aria-current="page">Delete</li>
@endsection

@section('content')
    <h2>Delete an account type</h2>

    <form method="POST" action="{{ route('admin.account.type.destroy', [$type->id]) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>You are going to permanently delete the following type. Please confirm your action.</p>
            <p><b>{{ $type->key }}</b></p>
        </div>
        <div>
            <input class="btn" type="submit" value="Delete">
        </div>
    </form>
@endsection
