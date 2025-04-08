@extends('layouts.main', ['welcome' => true])

@section('content')
<section class="documentation">
    {{-- This view is only a wrapper for the markdown page --}}
    {!! $documentation !!}
</section>
@endsection