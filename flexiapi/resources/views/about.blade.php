@extends('layouts.main', ['welcome' => true])

@section('content')
    <div style="max-width: 40rem; width: 100%; padding: 1rem;">
        <img style="float: right; margin-top: 1rem;" src="{{ asset('img/logo_linphone.png') }}">
        <h2>About</h2>
        <hr />

        @if (!empty(config('app.project_url')))
            <p><a href="{{ config('app.project_url') }}">{{ config('app.project_url') }}</a></p>
        @endif

        <p><a href="{{ config('app.terms_of_use_url') }}">Terms and Conditions</a> and <a
                href="{{ config('app.privacy_policy_url') }}">Privacy policy</a></p>

        <p><a href="{{ route('api') }}">API Documentation</a>, <a href="{{ route('provisioning.documentation') }}">Provisioning Documentation</a> and <a href="{{ route('account.documentation') }}">General Documentation</a></p>

        <p>GNU General Public Licence v3.0 (Licence)</p>

        <p>{{ config('instance.copyright') }}</p>
    </div>
@endsection
