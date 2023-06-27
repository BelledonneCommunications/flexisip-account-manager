@extends('layouts.main')

@section('content')
    <div>
        <a class="btn oppose btn-secondary" href="{{ route('admin.account.delete', $account->id) }}">
            <i class="material-icons">delete</i>
            Delete
        </a>
        @if ($account->id)
            <h1><i class="material-icons">people</i> Edit an account</h1>
            <p title="{{ $account->updated_at }}">Updated on {{ $account->updated_at->format('d/m/Y')}}
        @else
            <h1><i class="material-icons">people</i> Create an account</h1>
        @endif
    </div>

    <form method="POST"
        action="{{ $account->id ? route('admin.account.update', $account->id) : route('admin.account.store') }}"
        accept-charset="UTF-8">
        @csrf
        @method($account->id ? 'put' : 'post')
        <h2>Connexion</h2>
        <div>
            <input placeholder="Username" required="required" name="username" type="text" value="{{ $account->username }}">
            <label for="username">Username</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div>
            <input placeholder="domain.com" @if (config('app.admins_manage_multi_domains')) required @else disabled @endif name="domain" type="text" value="{{ $account->domain ?? config('app.sip_domain') }}">
            <label for="domain">Domain</label>
        </div>

        <div>
            <input placeholder="John Doe" name="display_name" type="text" value="{{ $account->display_name }}">
            <label for="display_name">Display Name</label>
            @include('parts.errors', ['name' => 'display_name'])
        </div>
        <div></div>

        <div>
            <input placeholder="Password" name="password" type="password" value="">
            <label for="password">{{ $account->id ? 'Password (fill to change)' : 'Password' }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>

        <div>
            <input placeholder="Password" name="password_confirmation" type="password" value="">
            <label for="password_confirmation">Confirm password</label>
            @include('parts.errors', ['name' => 'password_confirmation'])
        </div>


        <div>
            <input placeholder="Email" name="email" type="email" value="{{ $account->email }}">
            <label for="email">Email</label>
            @include('parts.errors', ['name' => 'email'])
        </div>

        <div>
            <input placeholder="+12123123" name="phone" type="text" value="{{ $account->phone }}">
            <label for="phone">Phone</label>
            @include('parts.errors', ['name' => 'phone'])
        </div>

        <h2>Other information</h2>

        <div>
            <input name="activated" type="checkbox" @if ($account->activated)checked @endif>
            <label>Activated</label>
        </div>

        <div>
            <input name="role" value="admin" type="radio" @if ($account->admin)checked @endif>
            <p>Admin</p>
            <input name="role" value="end_user" type="radio" @if (!$account->admin)checked @endif>
            <p>End user</p>
            <label>Role</label>
        </div>

        <div class="select">
            <select name="dtmf_protocol">
                @foreach ($protocols as $value => $name)
                    <option value="{{ $value }}" @if( $account->dtmf_protocol == $value )selected="selected"@endif>{{ $name }}</option>
                @endforeach
            </select>
            <label for="dtmf_protocol">DTMF Protocol</label>
        </div>

        <hr>
        <div class="large">
            <input class="btn oppose" type="submit" value="{{ $account->id ? 'Update' : 'Create' }}">
        </div>

    </form>
@endsection
