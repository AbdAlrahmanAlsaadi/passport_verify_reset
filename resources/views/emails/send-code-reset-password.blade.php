@component('mail::message')
{{-- عنوان الرسالة --}}
# {{ __('Verification Code') }}

{{-- نص الوصف --}}
<p style="font-size: 18px; color: #2d3748; text-align: center;">
    We’re excited to have you with us! To verify your email, please use the code below.
</p>

{{-- رمز التحقق --}}
@component('mail::panel')
<div style="font-size: 24px; font-weight: bold; color: #4A90E2; text-align: center;">
    {{ __('Your Verification Code:') }}
</div>
<div style="font-size: 30px; font-weight: bold; color: #FF6F61; text-align: center; padding-top: 10px;">
    {{ $code }}
</div>
@endcomponent

{{-- ملاحظة اضافية --}}
<p style="font-size: 14px; color: #6B7280; text-align: center;">
    This code will expire in 10 minutes. Please verify your email before it expires.
</p>

{{-- خاتمة --}}
Thanks,<br>
{{ config('app.name') }}

{{-- نص فرعي --}}
@slot('footer')
@component('mail::subcopy')
    If you’re having trouble using the verification code, please contact our support team.
@endcomponent
@endslot

@endcomponent
