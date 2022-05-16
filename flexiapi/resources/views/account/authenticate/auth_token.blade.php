@extends('layouts.main')

@section('content')
    @if (Auth::check())
        @include('parts.already_auth')
    @else
        <p class="text-center pt-3">Scan the following QR Code using an authenticated device and wait a few seconds.</p>
        <p class="text-center pt-3"><img src="{{ route('auth_tokens.qrcode', ['token' => $authToken->token]) }}"></p>
        <script type="text/javascript">
            setTimeout(function () { location.reload(1); }, 5000);
        </script>
    @endif
@endsection