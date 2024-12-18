@if (captchaConfigured())
    <div class="large">
        <script src="https://hcaptcha.com/1/api.js?recaptchacompat=off" async="" defer=""></script>
        <x-hcaptcha::widget />
        @include('parts.errors', ['name' => 'h-captcha-response'])
    </div>
@endif
