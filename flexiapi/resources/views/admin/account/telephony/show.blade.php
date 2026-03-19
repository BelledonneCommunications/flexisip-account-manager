@extends('layouts.main')

@section('breadcrumb')
    @include('admin.parts.breadcrumb.accounts.show', ['account' => $account])
@endsection

@section('content')
    <header>
        <h1><i class="ph ph-phone"></i> {{ $account->identifier }}</h1>
    </header>
    @include('admin.account.parts.tabs')

    <div class="grid">
        <div class="card">
            @include('account.call_forwardings.edit', ['account' => $account])
        </div>
    </div>
@endsection