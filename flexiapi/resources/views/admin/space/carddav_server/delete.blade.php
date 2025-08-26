@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item active" aria-current="page">{{ __('CardDav Server') }} - {{ __('Delete' ) }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-trash"></i> {{ __('CardDav Server') }} - {{ __('Delete') }}</h1>

        <a href="{{ route('admin.spaces.integration', ['space' => $space]) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>
    <form id="delete" method="POST" action="{{ route('admin.spaces.carddavs.destroy', [$carddavServer->space_id, $carddavServer->id]) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}<br />
                <b><i class="ph ph-identification-card"></i> {{ $carddavServer->uri }}</b>
            </p>
            <input name="account_id" type="hidden" value="{{ $carddavServer->space_id }}">
        </div>
        <div>
        </div>
    </form>
@endsection
