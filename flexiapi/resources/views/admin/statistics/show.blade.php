@extends('layouts.main')

@section('content')
    <header>
        <h1><i class="ph ph-chart-donut"></i> {{ __('Statistics') }}</h1>
    </header>

    @include('admin.statistics.parts.tabs')

    @include('admin.statistics.parts.filters')
    @include('parts.graph')
@endsection
