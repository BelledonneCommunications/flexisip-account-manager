@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.index')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.account.type.index') }}">{{ __('Types') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        @if ($type->id){{ __('Edit') }} @else {{ __('Create') }} @endif
    </li>
@endsection

@section('content')
    @if ($type->id)
        <h2>{{ __('Edit') }}</h2>
    @else
        <h2>{{ __('Create') }}</h2>
    @endif

    <form method="POST"
        action="{{ $type->id ? route('admin.account.type.update', $type->id) : route('admin.account.type.store') }}"
        accept-charset="UTF-8">
        @method($type->id ? 'put' : 'post')
        @csrf
        <div>
            <input type="text" name="key" value="{{ $type->key }}" placeholder="type_key">
            <label for="key">{{ __('Key') }}</label>
        </div>

        <div>
            <input class="btn btn-success" type="submit" value="{{ $type->id ? __('Update') : __('Create') }}">
        </div>
    </form>
@endsection
