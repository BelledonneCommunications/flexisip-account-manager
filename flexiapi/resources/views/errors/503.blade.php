@extends('errors::minimal')

@section('code', '503')

@if (app()->isDownForMaintenance())
    @section('title', __('We will be back soon!'))
    @section('message', 'Sorry for the inconvenience but we are performing some maintenance at the moment.')
@else
    @section('title', __('Service Unavailable'))
    @section('message', $exception->getMessage())
@endif
