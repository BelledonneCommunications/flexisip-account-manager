@extends('errors::minimal')

@section('title', __('Bad Request'))
@section('code', '404')
@section('message', __($exception->getMessage() ?: 'Bad Request'))
