@if (captchaConfigured())
    <div class="large">
        {!! NoCaptcha::renderJs() !!}
        {!! NoCaptcha::display() !!}
        @include('parts.errors', ['name' => 'g-recaptcha-response'])
    </div>
@endif
