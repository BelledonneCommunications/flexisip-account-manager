@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Delete') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">trash</i> Delete my account</h1>

        <a href="{{ route('account.dashboard') }}" class="btn btn-secondary oppose">{{ __('Cancel') }}</a>
        <input form="delete" class="btn" type="submit" value="{{ __('Delete') }}">
    </header>

    <form id="delete" method="POST" action="{{ route('account.destroy') }}" accept-charset="UTF-8">
        @csrf
        @method('delete')

        <div class="large">
            <p>{{ __('You are going to permanently delete your account. Please enter your complete SIP address to confirm.') }}</p>
            <p><b>{{ $account->identifier }}</b></p>
        </div>

        <div>
            <input placeholder="bob@example.net" name="identifier" type="text" value="{{ old('identifier') }}">
            <label for="identifier">SIP address</label>
        </div>

        <div class="on_desktop"></div>

        <input name="identifier_confirm" type="hidden" value="{{ $account->identifier }}">

        <div>
        </div>
    </form>
@endsection
