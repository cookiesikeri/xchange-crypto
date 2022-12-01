<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Muli:400,300" rel="stylesheet">
    <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'>
</head>
<body>
    <div class="image-container set-full-height">
        <br><br><br><br>
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6" style="visibility:hidden">
                            <address>
                                <strong>TaheerXchange Nigeria</strong>
                                <br>
                                    Email: hello@taheerexchange.com
                                <br>
                                <abbr title="Phone">Tel:</abbr> 0813-234-4213
                            </address>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6 text-right">
                            <p>
                                <em id="receipt_date">Date: </em>
                            </p>
                            <p>
                                <em id="receipt_trans_id">Transaction ID #: {{$airtimePurchase->transaction_id}}</em>
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-md-9">
                                        <b id="receipt_service_id">
                                            Airtime Purchase
                                            @if($airtimePurchase->service_id == 1)
                                            MTN
                                            @elseif($airtimePurchase->service_id == 2)
                                            Glo
                                            @elseif($airtimePurchase->service_id == 3)
                                            Airtel
                                            @else
                                            9Mobile
                                            @endif
                                        </b>
                                    </td>
                                    <td class="col-md-1 text-center" id="receipt_amount">₦{{ $airtimePurchase->amount }}</td>

                                        <td class="col-md-1 text-center" id="receipt_total">₦{{$airtimePurchase->amount_paid}}</td>

                                </tr>
                                <tr>
                                    <td>   </td>
                                    <td class="text-right"><h4><strong>Total: </strong></h4></td>

                                        <td class="text-center text-danger"><h4><strong id='receipt_total_purchase'>₦{{ $airtimePurchase->amount }}</strong></h4></td>

                                </tr>
                            </tbody>
                        </table>
                        <div>
                            <h4 style="text-align:center;">
                                Thank you for your patronage. We hope to see you again...
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="container text-center">
                TaheerXchange &copy; {{ date('Y') }}
            </div>
        </div>
    </div>
</body>
<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js'></script>
</html>
