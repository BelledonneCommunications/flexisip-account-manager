@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active">{{ __('External Account') }}</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-user-circle-dashed"></i> {{ __('External Account') }}</h1>
        @if($externalAccount->id)
            <a class="btn secondary oppose" href="{{ route('admin.account.external.delete', $account->id) }}">
                <i class="ph ph-trash"></i>
                {{ __('Delete') }}
            </a>
        @endif
    </header>
    @include('admin.account.parts.tabs')

    <form method="POST"
        action="{{ route('admin.account.external.store', $account->id) }}"
        id="show" accept-charset="UTF-8">
        @csrf
        @method('post')
        <h3 class="large">{{ __('Connection') }}</h3>
        <div>
            <input placeholder="username" required="required" name="username" type="text"
                value="@if($externalAccount->id){{ $externalAccount->username }}@else{{ old('username') }}@endif">
            <label for="username">{{ __('Username') }}</label>
            @include('parts.errors', ['name' => 'username'])
        </div>
        <div>
            <input placeholder="domain.tld" required="required" name="domain" type="text"
                value="@if($externalAccount->id){{ $externalAccount->domain }}@else{{ old('domain') }}@endif">
            <label for="domain">{{ __('Domain') }}</label>
            @include('parts.errors', ['name' => 'domain'])
        </div>

        <div>
            <input placeholder="Password" name="password" type="password" value="" autocomplete="new-password"
                @if (!$externalAccount->id) required @endif>
            <label for="password">{{ __('Password') }} @if ($externalAccount->id) ({{ __('Currently set') }}) @endif</label>
            @if($externalAccount->id)<small>{{ __('Fill to change') }}</small>@endif
            @include('parts.errors', ['name' => 'password'])
        </div>

        <details class="large" @if ($errors->isNotEmpty())open @endif>
            <summary>
                <h3 class="large">
                    {{ __('Other information') }}
                </h3>
            </summary>
            <section>
                <div>
                    <input placeholder="realm" name="realm" type="text"
                        value="@if($externalAccount->id){{ $externalAccount->realm }}@else{{ old('realm') }}@endif">
                    <label for="username">{{ __('Realm') }}</label>
                    @include('parts.errors', ['name' => 'realm'])
                </div>
                <div>
                    <input placeholder="domain.tld" name="registrar" type="text"
                        value="@if($externalAccount->id){{ $externalAccount->registrar }}@else{{ old('registrar') }}@endif">
                    <label for="domain">{{ __('Registrar') }}</label>
                </div>
                <div>
                    <input placeholder="outbound.tld" name="outbound_proxy" type="text"
                        value="@if($externalAccount->id){{ $externalAccount->outbound_proxy }}@else{{ old('outbound_proxy') }}@endif">
                    <label for="domain">{{ __('Outbound Proxy') }}</label>
                </div>
                <div class="select">
                    <select name="protocol">
                        @foreach ($protocols as $protocol)
                            <option value="{{ $protocol }}" @if ($externalAccount->protocol == $protocol) selected="selected" @endif>
                                {{ $protocol }}</option>
                        @endforeach
                    </select>
                    <label for="dtmf_protocol">{{ __('Protocol') }}</label>
                </div>
            </section>
        </details>
    </form>

    <br />

    <input form="show" class="btn" type="submit" value="@if($externalAccount->id){{ __('Update') }}@else{{ __('Create') }}@endif">
@endsection