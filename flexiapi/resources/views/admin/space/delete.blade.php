@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.spaces.index') }}">{{ __('Spaces') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-trash"></i> {{ __('Delete') }}</h1>
        <a href="{{ route('admin.spaces.edit', $space->id) }}" class="btn secondary oppose">{{ __('Cancel') }}</a>
    </header>
    <form id="delete" method="POST" action="{{ route('admin.spaces.destroy', $space) }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete the following element. Please confirm your action.') }}</p>
            <h3>{{ $space->domain }}</h3>
            <p>This will also destroy <b>{{ $space->accounts()->count() }} related accounts</b></p>

            <input name="sip_domain" type="hidden" value="{{ $space->id }}">
        </div>

        <div>
            <input placeholder="domain.tld" required="required" name="domain" type="text">
            <label for="username">Please retype the domain here to confirm</label>
            @include('parts.errors', ['name' => 'domain'])
        </div>

        <div class="large">
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection
