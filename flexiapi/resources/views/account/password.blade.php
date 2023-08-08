@extends('layouts.main')

@section('content')
    @if ($account->passwords()->count() > 0)
        <h2>Change my account password</h2>
    @else
        <h2>Set my account password</h2>
    @endif

    <form method="POST" action="{{ route('account.password.update') }}" accept-charset="UTF-8">
        @csrf

        <div>
            <input type="password" name="password" required>
            <label for="password">New password</label>
        </div>
        <div>
            <input type="password_confirmation" name="password_confirmation" required>
            <label for="password_confirmation">Password confirmation</label>
        </div>
        <div>
            <input type="checkbox" name="password_sha256" checked>
            <label for="password_sha256">Use a SHA-256 encrypted password. This stronger password might not work with some
                old SIP clients</label>
        </div>

        <input class="btn btn-primary" type="submit" value="Change">
    </form>
@endsection
