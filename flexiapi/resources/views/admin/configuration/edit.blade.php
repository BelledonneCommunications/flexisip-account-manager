@extends('layouts.account')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Configuration</li>
@endsection

@section('content')
<h2>Configuration</h2>

{!! Form::open(['route' => 'admin.configuration.update']) !!}
    <div class="form-row">
        <div class="form-group col-md-6">
            {!! Form::label('copyright', 'Copyright') !!}
            {!! Form::text('copyright', $configuration->copyright, ['class' => 'form-control', 'placeholder' => 'Copyright']) !!}
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            {!! Form::label('intro_registration', 'Introduction text during Registration') !!}
            {!! Form::textarea('intro_registration', $configuration->intro_registration, ['class' => 'form-control', 'placeholder' => 'Introduction text during registration']) !!}
        </div>
    </div>
    <div class="form-check mb-3">
        {!! Form::checkbox('custom_theme', 'checked', $configuration->custom_theme, ['class' => 'form-check-input', 'id' => 'custom_theme']) !!}
        <label class="form-check-label" for="custom_theme">CSS spéficique</a></label>
    </div>
    <div class="form-row">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
{!! Form::close() !!}

@endsection