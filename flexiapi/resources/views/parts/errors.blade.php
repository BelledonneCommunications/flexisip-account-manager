@if (isset($errors) && $errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif