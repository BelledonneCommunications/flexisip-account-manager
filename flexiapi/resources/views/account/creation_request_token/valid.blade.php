@extends('layouts.main', ['welcome' => true])

@section('content')
    <h3 class="text-center mt-5">{{ __('Thanks for the validation') }}</h3>
    <p class="text-center">{{ __('You can now continue your registration process in the application') }}</p>
@endsection