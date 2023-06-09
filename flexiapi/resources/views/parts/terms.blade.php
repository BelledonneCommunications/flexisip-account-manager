<div class="large checkbox">
    {!! Form::checkbox('terms', 'true', false, ['id' => 'terms']) !!}
    <label for="terms">I accept the Terms and Conditions: </a>
        Read the <a href="{{ config('app.terms_of_use_url') }}">Terms and Conditions</a>
    </label>
    @include('parts.errors', ['name' => 'terms'])
</div>

<div class="large checkbox">
    {!! Form::checkbox('privacy', 'true', false, ['id' => 'privacy']) !!}
    <label for="privacy">I accept the Privacy policy: </a>
        Read the <a href="{{ config('app.privacy_policy_url') }}">Privacy policy</a>
    </label>
    @include('parts.errors', ['name' => 'privacy'])
</div>

@include('parts.captcha')
