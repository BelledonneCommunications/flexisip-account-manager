@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.index')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.type.index') }}">{{ __('Types') }}</a>
    </li>
    <li class="breadcrumb-item">
        {{ $type->key }}
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <h2>{{ __('Delete') }}</h2>

    <form method="POST" action="{{ route('admin.account.type.destroy', [$type->id]) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div>
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
            <p><b>{{ $type->key }}</b></p>
        </div>
        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection
