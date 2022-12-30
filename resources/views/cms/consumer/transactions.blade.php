@extends('layouts.admin')
@section('title')
Wallet Transaction Manager
@endsection
@section('content')

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-warning">
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="header">
                        <h2> Wallet Transaction Manager </h2>
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more-vert"></i> </a>
                                <ul class="dropdown-menu pull-right">
                                    <li><a href="">Refresh</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover dataTable js-exportable" id="example">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Wallet ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Wallet ID</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    @if(count($user_withdrawers) < 1)

                                    <tr>
                                        <th>No record currently available</th>
                                    </tr>

                                    @else
                                    @foreach($user_withdrawers as $key=>$credit_wallet_transaction)
                                    <td>{{++$key}}</td>
                                    <td>{{ucWords(App\Models\WalletTransaction::find($credit_wallet_transaction->wallet_id->user_id)->username)}}</td>
                                    <td>{{$debit_wallet_transaction->type}}</td>
                                    <td><font color="red"> ₦{{number_format($debit_wallet_transaction->amount)}}</font></td>
                                    <td>{{$debit_wallet_transaction->created_at->toDayDateTimeString()}}</td>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 



</section>


@endsection
