@extends('layouts.main', ['grid' => true])

@section('content')
    <header>
        <h1><i class="material-icons-outlined">dashboard</i> Dashboard</h1>
    </header>

    <div class="large">
        <h2><i class="material-icons-outlined">key</i>API Key</h2>

        <p>You can generate an API key and use it to request the different API endpoints, <a href="{{ route('api') }}">check
                the related API documentation</a> to know how to use that key.</p>

        <form method="POST" action="{{ route('account.api_key.update') }}" accept-charset="UTF-8">
            @csrf
            <div>
                <input readonly placeholder="No key yet, press Generate"
                    @if ($account->apiKey) value="{{ $account->apiKey->key }}" @endif>
                <label>Key</label>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Generate</button>
            </div>
        </form>
    </div>
@endsection
