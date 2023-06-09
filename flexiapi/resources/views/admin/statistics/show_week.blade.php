@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">
    Statistics
</li>
@endsection

@section('content')

<ul class="nav justify-content-center">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.statistics.show.day') }}">Day</a>
    </li>
    <li class="nav-item">
        <a class="nav-link disabled" href="#">Week</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.statistics.show.month') }}">Month</a>
    </li>
</ul>

<h2>Statistics</h2>

@include('admin.statistics.parts.legend')

<h3>Week</h3>

<div class="columns">
@foreach ($week as $key => $day)
    <div class="column" data-value="{{ $key }}">
        @include('admin.statistics.parts.columns', ['slice' => $day, 'max' => $max_week])
    </div>
@endforeach
</div>

@endsection