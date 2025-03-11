@props([
    'site_key',
    'extension'
])

@if ($extension->status ?? false)

    <div class="mb-4 d-flex justify-content-center">
        <div class="g-recaptcha" data-sitekey="{{ $site_key }}" data-theme="light" data-callback="googleV2CaptchaCallback"></div>
    </div>

    @push('css')
        
    @endpush

    @push('script')
        <script>
            function googleV2CaptchaCallback(token){
                // handle token
            }
        </script>
    @endpush
@endif
