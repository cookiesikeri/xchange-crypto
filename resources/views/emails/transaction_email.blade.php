@component('mail::message')
    <h3>Hi {{ $user }} </h3>

   Your wallet/bank transfer was successful.<br>


    N,{{ $amount }} has been credited to your account and received by Eben.
    Please find the transaction summary below.


@component('mail::button', ['url' => 'https://taheerxchange.com'])
Go to Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
