@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('account.device.index') }}">{{ __('Devices') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <h2>{{ __('Delete') }}</h2>

    <div>
        <p>Are you sure you want to delete the following device?</p>
        <p>
            <b>User Agent:</b> {{ $device->user_agent }}
        </p>
    </div>

    <form method="POST" action="{{ route('account.device.destroy') }}" accept-charset="UTF-8">
        @method('delete')
        @csrf
        <input name="uuid" type="hidden" value="{{ $device->uuid }}">
        <div>
            <input class="btn" type="submit" value="{{ __('Delete') }}">
        </div>
    </form>
@endsection
