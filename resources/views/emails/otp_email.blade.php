@component('mail::message')
Dear {{$name}}

Verify your email to complete the sign up process {{$otp}},  Below is your OTP;

If you did not request this, you can ignore this email or contact us at 09126397198
thank you!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
