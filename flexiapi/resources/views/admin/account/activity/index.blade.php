@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">Activity</li>
@endsection

@section('content')

<header>
    <h1><i class="ph">list</i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

@if ($account->apiKey)
    <h3>Api Key</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>IP</th>
                <th>{{ __('Requests') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $account->apiKey->key }}
                </td>
                <td>
                    {{ $account->apiKey->created_at }}
                </td>
                <td>
                    {{ $account->apiKey->last_used_at }}
                </td>
                <td>
                    {{ $account->apiKey->ip ?? '*' }}
                </td>
                <td>
                    {{ $account->apiKey->requests }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if ($account->accountCreationToken)
    <h3>Account Creation Token</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <tr @if ($account->accountCreationToken->offed()) class="disabled crossed" @endif>
                <td>****</td>
                <td>
                    {{ $account->accountCreationToken->created_at }}
                </td>
                <td>
                    {{ $account->accountCreationToken->created_at != $account->accountCreationToken->updated_at ? $account->accountCreationToken->updated_at : '-' }}
                </td>
                <td title="{{ $account->accountCreationToken->user_agent }}">
                    {{ $account->accountCreationToken->ip ? $account->accountCreationToken->ip : '-' }}
                </td>
            </tr>
        </tbody>
    </table>
@endif

@if ($account->recoveryCodes->isNotEmpty())
    <h3>Recovery Codes</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->recoveryCodes as $key => $recoveryCode)
                <tr @if ($recoveryCode->offed() || $key > 0) class="disabled crossed" @endif>
                    <td>****</td>
                    <td>
                        {{ $recoveryCode->created_at }}
                    </td>
                    <td>
                        {{ $recoveryCode->created_at != $recoveryCode->updated_at ? $recoveryCode->updated_at : '-' }}
                    </td>
                    <td title="{{ $recoveryCode->user_agent }}">
                        {{ $recoveryCode->ip ? $recoveryCode->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->phoneChangeCodes->isNotEmpty())
    <h3>Phone Change requests</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('Phone number') }}</th>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->phoneChangeCodes as $key => $phoneChangeCode)
                <tr @if ($phoneChangeCode->offed() || $key > 0) class="disabled crossed" @endif>
                    <td>{{ $phoneChangeCode->phone }}</td>
                    <td>{{ $phoneChangeCode->code ?? '-' }}</td>
                    <td>
                        {{ $phoneChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $phoneChangeCode->created_at != $phoneChangeCode->updated_at ? $phoneChangeCode->updated_at : '-' }}
                    </td>
                    <td title="{{ $phoneChangeCode->user_agent }}">
                        {{ $phoneChangeCode->ip ? $phoneChangeCode->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->emailChangeCodes->isNotEmpty())
    <h3>Email Change requests</h3>
    <table>
        <thead>
            <tr>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->emailChangeCodes as $key => $emailChangeCode)
                <tr @if ($emailChangeCode->offed() || $key > 0) class="disabled crossed" @endif>
                    <td>{{ $emailChangeCode->email }}</td>
                    <td>{{ $emailChangeCode->code ?? '-' }}</td>
                    <td>
                        {{ $emailChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $emailChangeCode->created_at != $emailChangeCode->updated_at ? $emailChangeCode->updated_at : '-' }}
                    </td>
                    <td title="{{ $emailChangeCode->user_agent }}">
                        {{ $emailChangeCode->ip ? $emailChangeCode->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->provisioningTokens->isNotEmpty())
    <h3>{{ __('Provisioning tokens') }}</h3>
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->provisioningTokens as $key => $provisioningToken)
                <tr @if ($provisioningToken->offed() || $key > 0) class="disabled crossed" @endif>
                    <td>{{ $provisioningToken->token }}</td>
                    <td>
                        {{ $provisioningToken->created_at }}
                    </td>
                    <td>
                        {{ $provisioningToken->consumed() ? $provisioningToken->updated_at : '-' }}
                    </td>
                    <td title="{{ $provisioningToken->user_agent }}">
                        {{ $provisioningToken->ip ? $provisioningToken->ip : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->resetPasswordEmailTokens->isNotEmpty())
    <h3>Set Password Emails</h3>
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>{{ __('Created on') }}</th>
                <th>{{ __('Used on') }}</th>
                <th>{{ __('Email') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->resetPasswordEmailTokens as $key => $resetPasswordEmailToken)
                <tr @if ($resetPasswordEmailToken->offed() || $key > 0) class="disabled crossed" @endif>
                    <td>{{ $resetPasswordEmailToken->token }}</td>
                    <td>
                        {{ $resetPasswordEmailToken->created_at }}
                    </td>
                    <td>
                        {{ $resetPasswordEmailToken->consumed() ? $resetPasswordEmailToken->updated_at : '-' }}
                    </td>
                    <td>
                        {{ $resetPasswordEmailToken->email }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection