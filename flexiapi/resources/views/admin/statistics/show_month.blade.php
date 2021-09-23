@extends('layouts.account')

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
        <a class="nav-link" href="{{ route('admin.statistics.show.week') }}">Week</a>
    </li>
    <li class="nav-item">
        <a class="nav-link disabled" href="#">Month</a>
    </li>
</ul>

<h2>Statistics</h2>

@include('admin.statistics.parts.legend')

<h3>Month</h3>

<div class="columns">
@foreach ($month as $key => $day)
    <div class="column" data-value="{{ $key }}">
        @include('admin.statistics.parts.columns', ['slice' => $day, 'max' => $max_month])
    </div>
@endforeach
</div>

@endsection