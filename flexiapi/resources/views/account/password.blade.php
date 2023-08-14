@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Change password</li>
@endsection

@section('content')
    <header>
        @if ($account->passwords()->count() > 0)
            <h1><i class="material-icons-outlined">lock</i> Change password</h1>
        @else
            <h1><i class="material-icons-outlined">lock</i> Set password</h1>
        @endif

        <a href="{{ route('account.dashboard') }}" class="btn btn-secondary oppose">Cancel</a>
        <input form="password_updated" class="btn" type="submit" value="Change">
    </header>

    <form id="password_update" method="POST" action="{{ route('account.password.update') }}" accept-charset="UTF-8">
        @csrf

        <div>
            <input type="password" name="password" required>
            <label for="password">New password</label>
        </div>
        <div>
            <input type="password_confirmation" name="password_confirmation" required>
            <label for="password_confirmation">Password confirmation</label>
        </div>
    </form>
@endsection
