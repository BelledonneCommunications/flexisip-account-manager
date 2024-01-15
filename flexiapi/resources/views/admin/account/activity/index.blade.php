@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_edit', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">Activity</li>
@endsection

@section('content')

<header>
    <h1><i class="material-symbols-outlined">view_list</i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

@if ($account->recoveryCodes->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Recovery Codes</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->recoveryCodes as $recoveryCode)
                <tr @if ($recoveryCode->consumed()) class="disabled crossed" @endif>
                    <td>****</td>
                    <td>
                        {{ $recoveryCode->created_at }}
                    </td>
                    <td>
                        {{ $recoveryCode->created_at != $recoveryCode->updated_at ? $recoveryCode->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->phoneChangeCodes->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Phone Change requests</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->phoneChangeCodes as $phoneChangeCode)
                <tr @if ($phoneChangeCode->consumed()) class="disabled crossed" @endif>
                    <td>****</td>
                    <td>
                        {{ $phoneChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $phoneChangeCode->created_at != $phoneChangeCode->updated_at ? $phoneChangeCode->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->emailChangeCodes->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Email Change requests</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->emailChangeCodes as $emailChangeCode)
                <tr @if ($emailChangeCode->consumed()) class="disabled crossed" @endif>
                    <td>****</td>
                    <td>
                        {{ $emailChangeCode->created_at }}
                    </td>
                    <td>
                        {{ $emailChangeCode->created_at != $emailChangeCode->updated_at ? $emailChangeCode->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($account->provisioningTokens->isNotEmpty())
    <table class="third">
        <thead>
            <tr>
                <th>Provisioning Tokens</th>
                <th>Created</th>
                <th>Used</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($account->provisioningTokens as $provisioningToken)
                <tr @if ($provisioningToken->consumed()) class="disabled crossed" @endif>
                    <td>{{ $provisioningToken->token }}</td>
                    <td>
                        {{ $provisioningToken->created_at }}
                    </td>
                    <td>
                        {{ $provisioningToken->consumed() ? $provisioningToken->updated_at : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@endsection