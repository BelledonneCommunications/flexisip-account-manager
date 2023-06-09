@extends('layouts.main')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">
    Statistics
</li>
@endsection

@section('content')

<ul class="nav justify-content-center">
    <li class="nav-item">
        <a class="nav-link disabled" href="#">Day</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.statistics.show.week') }}">Week</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.statistics.show.month') }}">Month</a>
    </li>
</ul>

<h2>Statistics</h2>

@include('admin.statistics.parts.legend')

<h3>Day</h3>

<div class="columns">
    @foreach ($day as $key => $hour)
        <div class="column" data-value="{{ substr($key, -2, 2) }}:00">
            @include('admin.statistics.parts.columns', ['slice' => $hour, 'max' => $max_day])
        </div>
    @endforeach
</div>

@endsection