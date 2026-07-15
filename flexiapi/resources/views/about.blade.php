@extends('layouts.main', ['welcome' => true])

@section('content')
    <div style="max-width: 40rem; width: 100%; padding: 1rem;">
        <img style="float: right; margin-top: 1rem;" src="{{ asset('img/logo_linphone.png') }}">
        <h2>{{ __('About') }}</h2>
        <hr />

        @if (!empty(config('app.project_url')))
            <p><a href="{{ config('app.project_url') }}">{{ config('app.project_url') }}</a></p>
        @endif

        <p>
            <i class="ph ph-code"></i><a href="{{ route('api') }}">API Documentation</a> <br />
            <i class="ph ph-cloud-arrow-down"></i><a href="{{ route('provisioning.documentation') }}">Provisioning Documentation</a><br />
            <i class="ph ph-magic-wand"></i><a href="{{ route('wizard.documentation') }}">Wizard Documentation</a>
        </p>

        <hr />
        <p><a href="{{ config('app.terms_of_use_url') }}">Terms and Conditions</a> and <a
                href="{{ config('app.privacy_policy_url') }}">Privacy policy</a></p>

        <p><a href="{{ route('third_party_components') }}">Third-party components licenses</a></p>

        <p>GNU General Public Licence v3.0 (Licence)</p>

        <p>{{ space()->instance_copyright }}</p>
    </div>
@endsection
