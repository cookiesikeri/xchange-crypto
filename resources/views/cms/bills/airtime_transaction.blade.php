@extends('layouts.app')
@section('title')
    All Airtime Transactions
@endsection
@section('content')


<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-warning">
                <!-- /.box-header -->
                <div class="box-body">
                        {{-- <h4 class="box-title">  <small class="label label-info position-right">Total Passengers: {{ \App\Models\User::all()->where('account_type', '["passenger"]')->count() }}</small></h4> --}}
                        <h4 class="box-title">  <small class="label label-info position-right">Total Airtime Transactions: {{ $pasengercnt }}</small></h4>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (Session::has('success'))
                        <div class="alert alert-success" role="alert">{{Session::get('success')}}</div>
                       @endif
                            <table class="table table-bordered table-striped table-hover dataTable js-exportable" id="example">
                                <thead>
                                    <tr>
                                        <th>S/N</th>

                                        <th>Customer</th>
                                        <th>Transaction ID</th>
                                        <th>Status </th>
                                        <th> Phone</th>
                                        <th> Email</th>
                                        <th> Amount</th>
                                        <th> Amount Paid</th>
                                        <th>Payment Method</th>
                                        <th>Payment Ref</th>
                                        <th>Platform</th>
                                        <th>Created Date</th>
                                        <th>Modified Date</th>
                                        <th>Network</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>

                                        <th>Customer</th>
                                        <th>Transaction ID</th>
                                        <th>Status </th>
                                        <th> Phone</th>
                                        <th> Email</th>
                                        <th> Amount</th>
                                        <th> Amount Paid</th>
                                        <th>Payment Method</th>
                                        <th>Payment Ref</th>
                                        <th>Platform</th>
                                        <th>Created Date</th>
                                        <th>Modified Date</th>
                                        <th>Network</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                                <tbody>

                                    @if(count($contacts) < 1)

                                    <tr>
                                        <th colspan="9">No record currently available</th>
                                    </tr>

                                    @else
                                    @foreach($contacts as $key=>$state)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td>{{ucWords(App\Models\User::find($state->user_id)->name)}}</td>
                                        <td>{{ucfirst($state['transaction_id'])}}</td>
                                        <td>
                                            @if($state['status'] == 1)
                                                <small class= "text-white label label-success">successful</small>
                                                @else
                                                <small class= "text-white label label-danger">failed</small>
                                            @endif
                                            </td>
                                        <td>{{$state['phone']}}</td>
                                        <td>{{$state['email']}}</td>
                                        <td>₦{{number_format($state->amount, 2)}}</td>
                                        <td>₦{{number_format($state->amount_paid, 2)}}</td>
                                        <td>  <small class= "text-white label label-primary">{{$state['payment_method']}}</small></td>
                                        <td>{{$state['payment_ref']}}</td>
                                        <td>{{$state['platform']}}</td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state['created_at'])) }}</td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state['date_modified'])) }}</td>
                                        <td>
                                            @if($state['network_id'] == "mtn")
                                            <small class= "text-white label label-warning">MTN</small>
                                            @elseif($state['network_id'] == "glo")
                                            <small class= "text-white label label-success">GLO</small>
                                            @elseif($state['network_id'] == "airtel")
                                            <small class= "text-white label label-danger">AIRTEL</small>
                                            @elseif($state['network_id'] == "etisalat")
                                            <small class= "text-white label label-primary">9MOBILE</small>
                                        @endif
                                        </td>
                                        <td>
                                            <button type="button"  class="btn btn-danger" onclick="deleteContact({{ $state->id }})"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function deleteContact(id) {

event.preventDefault();

if (confirm("Are you sure?")) {

    $.ajax({
        url: '/delete/airtime/' + id,
        method: 'get',
        success: function(result){
            window.location.assign(window.location.href);
        }
    });

} else {

    console.log('Delete process cancelled');

}

}
</script>
@endsection
