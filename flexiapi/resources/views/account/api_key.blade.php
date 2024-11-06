@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">
        API Key
    </li>
@endsection

@section('content')
    <div class="large">
        <h2><i class="ph">key</i>API Key</h2>

        <p>You can generate an API key and use it to request the different API endpoints, <a href="{{ route('api') }}">check
                the related API documentation</a> to know how to use that key.</p>

        <p>An unused key will expires after some times.</p>

        @if ($account->apiKey)
            <h3>Current Api Key</h3>
            <form>
                <div>
                    <input type="text" readonly value="{{ $account->apiKey->key }}">
                    <label>Key</label>
                    <small>Can only be used from the following ip: {{ $account->apiKey->ip }} | {{ $account->apiKey->requests }} requests</small>
                </div>
            </form>
        @endif

        <form method="POST" action="{{ route('account.api_key.update') }}" accept-charset="UTF-8">
            @csrf
            <div>
                <button type="submit" class="btn btn-primary">@if ($account->apiKey)Refresh the current key @else Generate a new key @endif</button>
            </div>
        </form>
    </div>
@endsection
