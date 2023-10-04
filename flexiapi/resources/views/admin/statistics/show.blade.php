@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Statistics</li>
@endsection

@section('content')
    <header>
        <h1><i class="material-icons-outlined">analytics</i> Statistics</h1>
    </header>

    @include('parts.tabs', [
        'items' => [
            route('admin.statistics.show', ['type' => 'messages']) => 'Messages',
            route('admin.statistics.show', ['type' => 'accounts']) => 'Accounts',
            route('admin.statistics.show', ['type' => 'calls']) => 'Calls',
        ],
    ])

    @include('admin.statistics.parts.filters')

    @include('parts.graph')
@endsection
