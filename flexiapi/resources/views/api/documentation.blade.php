@extends('layouts.main', ['welcome' => true])

@section('content')
    <div>
        {{-- This view is only a wrapper for the markdown page --}}
        {!! $documentation !!}
    </div>
@endsection
