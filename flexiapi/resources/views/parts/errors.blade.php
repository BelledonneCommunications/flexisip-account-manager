@if (isset($errors) && isset($name) && count($errors->get($name)) > 0)
    @foreach ($errors->get($name) as $error)
        <small>
            {{ $error }}
        </small>
    @endforeach
@endif
