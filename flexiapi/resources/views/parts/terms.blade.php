<div class="large checkbox">
    {!! Form::checkbox('terms', 'true', false, ['id' => 'terms']) !!}
    <label for="terms">
        I accept the
        <a href="{{ config('app.terms_of_use_url') }}">Terms and Conditions</a>
        @if (config('app.privacy_policy_url', null) != null)
            and <a href="{{ config('app.privacy_policy_url') }}">Privacy policy</a>
        @endif
    </label>
    @include('parts.errors', ['name' => 'terms'])
</div>

@include('parts.captcha')
