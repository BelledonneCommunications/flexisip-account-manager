@extends('layouts.main')

@section('content')
    {{-- This view is only a wrapper for the markdown page --}}
    {!! $documentation !!}
@endsection