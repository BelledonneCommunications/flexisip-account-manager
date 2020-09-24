<div class="form-check mb-3">
    {!! Form::checkbox('terms', 'true', false, ['class' => 'form-check-input', 'id' => 'terms']) !!}
    <label class="form-check-label" for="terms">I accept the Terms and Conditions: </a></label>
    <p>Read the <a href="{{ route('account.terms') }}">Terms and Conditions</a></p>
</div>

<div class="form-check mb-3">
    {!! Form::checkbox('privacy', 'true', false, ['class' => 'form-check-input', 'id' => 'privacy']) !!}
    <label class="form-check-label" for="privacy">I accept the Privacy policy: </a></label>
    <p>Read the <a href="{{ route('account.privacy') }}">Privacy policy</a></p>
</div>

@include('parts.captcha')