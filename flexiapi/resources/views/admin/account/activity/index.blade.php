@extends('layouts.main')

@section('breadcrumb')
    @include('admin.account.parts.breadcrumb_accounts_index')
    @include('admin.account.parts.breadcrumb_accounts_show', ['account' => $account])
    <li class="breadcrumb-item active" aria-current="page">Activity</li>
@endsection

@section('content')

<header>
    <h1><i class="ph ph-list"></i> {{ $account->identifier }}</h1>
</header>

@include('admin.account.parts.tabs')

<div class="grid">
    @if ($account->apiKey)
        <div class="card large">
            <h3>Api Key</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>Key</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $account->apiKey->created_at }}
                            <small>
                                {{ __('Used on') }}: {{ $account->apiKey->last_used_at }}
                            </small>
                        </td>
                        <td>
                            {{ $account->apiKey->key }}
                            <small>
                                IP: {{ $account->apiKey->ip ?? '*' }} |
                                {{ __('Requests') }}: {{ $account->apiKey->requests }}
                            </small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if ($account->accountCreationToken)
        <div class="card large">
            <h3>Account Creation Token</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Used on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $account->accountCreationToken->created_at }}
                            <small>{{ $account->accountCreationToken->created_at ?? '-' }}</small>
                        </td>
                        <td>
                            {{ $account->accountCreationToken->created_at != $account->accountCreationToken->updated_at ? $account->accountCreationToken->updated_at : '-' }}
                            <small title="{{ $account->accountCreationToken->user_agent }}">
                                {{ \Illuminate\Support\Str::limit($account->accountCreationToken->user_agent, 20, $end='...') }}
                            </small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if ($account->accountRecoveryTokens->isNotEmpty())
        <div class="card large">
            <h3>Account Recovery Tokens</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Used on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($account->accountRecoveryTokens as $key => $accountRecoveryToken)
                        <tr>
                            <td>
                                {{ $accountRecoveryToken->created_at }}
                                <small @if ($accountRecoveryToken->consumed())class="crossed"@endif>
                                    {{ __('Token') }}: {{ $accountRecoveryToken->token }}
                                </small>
                            </td>
                            <td>
                                {{ $accountRecoveryToken->created_at != $accountRecoveryToken->updated_at ? $accountRecoveryToken->updated_at : '-' }}
                                <small title="{{ $accountRecoveryToken->user_agent }}">
                                    IP: {{ $accountRecoveryToken->ip ?? '-' }} |
                                    {{ \Illuminate\Support\Str::limit($accountRecoveryToken->user_agent, 20, $end='...') }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($account->recoveryCodes->isNotEmpty())
        <div class="card large">
            <h3>Recovery Codes</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Via') }} <i class="ph ph-phone</i>/"><i class="ph ph-envelope</i>"></th>
                        <th>{{ __('Used on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($account->recoveryCodes as $key => $recoveryCode)
                        <tr>
                            <td>
                                {{ $recoveryCode->created_at }}
                                <small @if ($recoveryCode->consumed())class="crossed"@endif>
                                    {{ __('Code') }}: {{ $recoveryCode->code ?? '-' }}
                                </small>
                            </td>
                            <td>
                                @if ($recoveryCode->phone)
                                    <i class="ph ph-phone"></i> {{ $recoveryCode->phone }}
                                @elseif($recoveryCode->email)
                                    <i class="ph ph-envelope"></i> {{ $recoveryCode->email }}
                                @endif
                            </td>
                            <td>
                                {{ $recoveryCode->created_at != $recoveryCode->updated_at ? $recoveryCode->updated_at : '-' }}
                                <small title="{{ $recoveryCode->user_agent }}">
                                    IP: {{ $recoveryCode->ip ?? '-' }} |
                                    {{ \Illuminate\Support\Str::limit($recoveryCode->user_agent, 20, $end='...') }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($account->phoneChangeCodes->isNotEmpty())
    <div class="card">
        <h3>Phone Change requests</h3>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Phone number') }}</th>
                    <th>{{ __('Used on') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($account->phoneChangeCodes as $key => $phoneChangeCode)
                    <tr>
                        <td>
                            {{ $phoneChangeCode->created_at }}
                            <small @if ($phoneChangeCode->consumed())class="crossed"@endif>
                                {{ __('Code') }}: {{ $phoneChangeCode->code ?? '-' }}
                            </small>
                        </td>
                        <td>
                            {{ $phoneChangeCode->phone }}
                        </td>
                        <td title="{{ $phoneChangeCode->user_agent }}">
                            {{ $phoneChangeCode->created_at != $phoneChangeCode->updated_at ? $phoneChangeCode->updated_at : '-' }}
                            <small>
                                IP: {{ $phoneChangeCode->ip ?? '-' }} |
                                {{ \Illuminate\Support\Str::limit($phoneChangeCode->user_agent, 20, $end='...') }}
                            </small>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if ($account->emailChangeCodes->isNotEmpty())
        <div class="card">
            <h3>Email Change requests</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Used on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($account->emailChangeCodes as $key => $emailChangeCode)
                        <tr>
                            <td>
                                {{ $emailChangeCode->created_at }}
                                <small @if ($emailChangeCode->consumed())class="crossed"@endif>
                                    {{ __('Code') }}: {{ $emailChangeCode->code ?? '-' }}
                                </small>
                            </td>
                            <td>
                                {{ $emailChangeCode->email }}
                            </td>
                            <td title="{{ $emailChangeCode->user_agent }}">
                                {{ $emailChangeCode->created_at != $emailChangeCode->updated_at ? $emailChangeCode->updated_at : '-' }}
                                <small>
                                    IP: {{ $emailChangeCode->ip ?? '-' }} |
                                    {{ \Illuminate\Support\Str::limit($emailChangeCode->user_agent, 20, $end='...') }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($account->provisioningTokens->isNotEmpty())
        <div class="card">
            <h3>{{ __('Provisioning tokens') }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Used on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($account->provisioningTokens as $key => $provisioningToken)
                        <tr>
                            <td>
                                {{ $provisioningToken->created_at }}
                                <small @if ($provisioningToken->offed())class="crossed"@endif>
                                    {{ $provisioningToken->token }}
                                </small>
                            </td>
                            <td>
                                {{ $provisioningToken->consumed() ? $provisioningToken->updated_at : '-' }}
                                <small title="{{ $provisioningToken->user_agent }}">
                                    IP: {{ $provisioningToken->ip ?? '-' }} |
                                    {{ \Illuminate\Support\Str::limit($provisioningToken->user_agent, 20, $end='...') }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($account->resetPasswordEmailTokens->isNotEmpty())
        <div class="card large">
            <h3>{{ __('Reset password emails') }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Used on') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($account->resetPasswordEmailTokens as $key => $resetPasswordEmailToken)
                        <tr>
                            <td>
                                {{ $resetPasswordEmailToken->created_at }}
                                <small @if ($resetPasswordEmailToken->offed())class="crossed"@endif>
                                    {{ $resetPasswordEmailToken->token }}
                                </small>
                            </td>
                            <td>
                                {{ $resetPasswordEmailToken->email }}
                            </td>
                            <td>
                                {{ $resetPasswordEmailToken->consumed() ? $resetPasswordEmailToken->updated_at : '-' }}
                                <small title="{{ $resetPasswordEmailToken->user_agent }}">
                                    IP: {{ $resetPasswordEmailToken->ip ?? '-' }} |
                                    {{ $resetPasswordEmailToken->user_agent ? \Illuminate\Support\Str::limit($resetPasswordEmailToken->user_agent, 20, $end='...') : '-' }}
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>

@endsection