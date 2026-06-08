@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.spaces.integration')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.sso.show', $space->id) }}">{{ __('SSO Server') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete')}}</li>
@endsection

@section('content')

    <header>
        <h1><i class="ph ph-trash"></i> {{ __('Delete') }}</h1>
        <a href="{{ route('admin.spaces.sso.show', $space->id) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>

    <form id="delete" method="POST" action="{{ route('admin.spaces.sso.destroy', $space) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')
        <h2><i class="ph ph-warning"></i> {{__('Warning: Irreversible Action')}}</h2>
        <div class="large">
            <p>{{ __('You are about to delete your SSO configuration. Proceeding with this action will result in the following issues:') }}</p>
            <ul>
                <li>{{ __('Immediate Disconnection: All users currently logged into applications via SSO will be disconnected.') }}</li>
                <li>{{ __('Password Reset Required: You will need to reset passwords for all automatically created users, as well as any users who do not know their local credentials.') }}</li>
            </ul>
        </div>
    </form>
    
@endsection