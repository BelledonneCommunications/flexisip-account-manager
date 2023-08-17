@extends('layouts.main', ['welcome' => true])

@section('content')
    <div style="max-width: 40rem; width: 100%; padding: 1rem;">
        <img style="float: right; margin-top: 1rem;" src="{{ asset('img/logo_linphone.png') }}">
        <h2>About</h2>
        <hr />

        <p><a href="https://linphone.org/">https://linphone.org</a></p>

        <p><a href="{{ config('app.terms_of_use_url') }}">Terms and Conditions</a> and <a
                href="{{ config('app.privacy_policy_url') }}">Privacy policy</a></p>

        <p>GNU General Public Licence v3.0 (Licence)</p>

        <p>{{ config('instance.copyright') }}</p>
    </div>
@endsection
