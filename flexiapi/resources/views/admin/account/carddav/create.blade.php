@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">
        {{ __('CardDav credentials') }}
    </li>
@endsection

@section('content')
    <h1><i class="ph ph-plus"></i> {{ __('CardDav credentials') }}</h1>

    <form method="POST" action="{{ route('admin.account.carddavs.store', $account->id) }}" accept-charset="UTF-8">
        @csrf
        <div class="select large">
            <select name="carddav_id">
                @foreach ($carddavServers as $carddavServer)
                    <option value="{{ $carddavServer->id }}">{{ $carddavServer->name }}</option>
                @endforeach
            </select>
            <label for="carddav_id">{{ __('CardDav Server') }}</label>
            @include('parts.errors', ['name' => 'carddav_id'])
        </div>

        <div>
            <input placeholder="Username" name="username" type="text" value="{{ old('username') }}" required>
            <label for="username">{{ __('Username') }}</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
            <div>
            <input placeholder="Username" name="domain" type="text" value="{{ old('domain') }}" required>
            <label for="domain">{{ __('Domain') }}</label>
            @include('parts.errors', ['name' => 'domain'])
        </div>
        <div>
            <input placeholder="Password" name="password" type="password" required>
            <label for="password">{{ __('Password') }}</label>
            @include('parts.errors', ['name' => 'password'])
        </div>

        <div class="select">
            <select name="algorithm">
                @foreach (passwordAlgorithms() as $value => $key)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </select>
            <label for="algorithm">{{ __('Algorithm') }}</label>
        </div>

        <div>
            <input class="btn btn-success" type="submit" value="{{ __('Add') }}">
        </div>
    </form>
@endsection
