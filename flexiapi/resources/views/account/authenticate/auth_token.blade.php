@extends('layouts.main', ['welcome' => true])

@section('content')
    <section>
        <p>Scan the following QR Code using an authenticated device and wait a few seconds.</p><br />
        <p><img src="{{ route('auth_tokens.qrcode', ['token' => $authToken->token]) }}"></p>
        <script type="text/javascript">
            setTimeout(function () { location.reload(1); }, 5000);
        </script>
    </section>
@endsection