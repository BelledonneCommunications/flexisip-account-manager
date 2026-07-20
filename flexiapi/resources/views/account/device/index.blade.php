@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Devices') }}</li>
@endsection

@section('content')

    <header>
        <h1><i class="ph ph-devices"></i> {{ __('Devices') }}</h1>
    </header>

    <div class="card">
        @if ($devices->isEmpty())
            <div class="empty"><i class="ph ph-devices"></i>
                <p>{{ __('No device') }}</p>
            </div>
        @else
            <ul>
                @foreach ($devices as $device)
                    <li>
                        <span class="icon"> <i class=" ph {{ $device->app_icon }}"></i></span>
                        <div class="content">
                            <span class="title">{{ $device->app_label . ' - ' . $device->version }}</span>
                            <span class="subtitle">{{ $device->user_agent }}</span>
                        </div>
                        <div class="meta">
                            <span class="subtitle">{{ __(':time ago', ['time' => $device->update_time->diffForHumans(now(), true)]) }} </span>
                            <a type="button" class="btn small oppose secondary" href="{{ route('account.device.delete', [$device->uuid]) }}">
                                <i class="ph ph-trash"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
