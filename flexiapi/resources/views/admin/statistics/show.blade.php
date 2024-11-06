@extends('layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Statistics</li>
@endsection

@section('content')
    <header>
        <h1><i class="ph">chart-donut</i> Statistics</h1>
    </header>

    @include('admin.statistics.parts.tabs')

    @include('admin.statistics.parts.filters')
    @include('parts.graph')
@endsection
