@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Email Server') }} - {{ __('Delete' ) }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-trash"></i> {{ __('Email Server') }} - {{ __('Delete') }}</h1>

        <a href="{{ route('admin.spaces.integration', ['space' => $space]) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>
    <form id="delete" method="POST" action="{{ route('admin.spaces.email.destroy', $space->id) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}<br />
                <b><i class="ph ph-envelope"></i> {{ $space->emailServer->host }}</b>
            </p>
            <input name="account_id" type="hidden" value="{{ $space->id }}">
        </div>
        <div>
        </div>
    </form>
@endsection
