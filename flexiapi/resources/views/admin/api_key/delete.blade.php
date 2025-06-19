@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.api_keys.index') }}">{{ __('API Keys') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('Delete') }}
    </li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-trash"></i> {{ __('Delete') }}</h1>
        <a href="{{ route('admin.api_keys.index') }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>

    <form id="delete" method="POST" action="{{ route('admin.api_keys.destroy', $api_key->key) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
            <p>
                <i class="ph ph-key"></i> <code>{{ $api_key->key }}</code>
            </p>
        </div>

        <input name="key" type="hidden" value="{{ $api_key->key }}">
    </form>
@endsection
