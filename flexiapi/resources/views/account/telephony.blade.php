@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="ph ph-phone"></i> {{ __('Telephony') }}</h1>
    </header>

    <div class="grid">
        <div class="card">
            @include('account.voicemails.index', ['account' => $account, 'admin' => false])
        </div>

        <div class="card">
            @include('account.call_forwardings.edit', ['account' => $account])
        </div>
    </div>
@endsection