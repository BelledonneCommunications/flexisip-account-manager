@extends('layouts.main', ['large' => true])

@section('content')
    {{-- This view is only a wrapper for the markdown page --}}
    {!! $documentation !!}
@endsection