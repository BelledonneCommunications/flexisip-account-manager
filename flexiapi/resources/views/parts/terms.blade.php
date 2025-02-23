<div class="large checkbox">
    <input id="terms" name="terms" type="checkbox">
    <label for="terms"></label>
    <div>
        <p>
            I accept the
            <a href="{{ config('app.terms_of_use_url') }}">Terms and Conditions</a>
            @if (config('app.privacy_policy_url', null) != null)
                and <a href="{{ config('app.privacy_policy_url') }}">Privacy policy</a>
            @endif
        </p>
        @include('parts.errors', ['name' => 'terms'])
    </div>
</div>

@include('parts.captcha')
