@extends('layouts.app')
@section('title')
    All Virtual Card Requests
@endsection
@section('content')


<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-warning">
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="header"></div>
                        {{-- <h4 class="box-title">  <small class="label label-info position-right">Total Passengers: {{ \App\Models\User::all()->where('account_type', '["passenger"]')->count() }}</small></h4> --}}
                        <h4 class="box-title">  <small class="label label-info position-right">Total Virtual Card Requests: {{ $pasengercnt }}</small></h4>
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
                                        <th>Brand</th>
                                        <th>Currency</th>
                                        <th>Issuer Country</th>
                                        <th>Status</th>
                                        <th>Customer ID </th>
                                        <th> AllowedCategories</th>
                                        <th> AlockedCategories</th>
                                        <th> ATM </th>
                                        <th> POS</th>
                                        <th> WEB</th>
                                        <th> Mobile</th>
                                        <th>WIthdrawer Limit</th>
                                        <th> WIthdrawer Limit Interval</th>
                                        <th> Send Pin</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Customer</th>
                                        <th>Brand</th>
                                        <th>Currency</th>
                                        <th>Issuer Country</th>
                                        <th>Status</th>
                                        <th>Customer ID </th>
                                        <th> AllowedCategories</th>
                                        <th> AlockedCategories</th>
                                        <th> ATM </th>
                                        <th> POS</th>
                                        <th> WEB</th>
                                        <th> Mobile</th>
                                        <th>WIthdrawer Limit</th>
                                        <th> WIthdrawer Limit Interval</th>
                                        <th> Send Pin</th>
                                        <th>Date</th>
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
                                        <td> <small class= "text-white label label-primary">{{ucfirst($state['brand'])}}</small></td>
                                        <td>{{ucfirst($state['currency'])}}</td>
                                        <td>{{ucfirst($state['issuerCountry'])}}</td>
                                        <td> <small class= "text-white label label-success">{{ucfirst($state['status'])}}</small></td>
                                        <td>{{ucfirst($state['customerId'])}}</td>
                                        <td>{{ucfirst($state['allowedCategories'])}}</td>
                                        <td>{{ucfirst($state['blockedCategories'])}}</td>
                                        <td>{{ucfirst($state['atm'])}}</td>
                                        <td>{{ucfirst($state['pos'])}}</td>
                                        <td>{{ucfirst($state['web'])}}</td>
                                        <td>{{ucfirst($state['mobile'])}}</td>
                                        <td>₦{{number_format($state->amount, 2)}}</td>
                                        <td>{{ucfirst($state['interval'])}}</td>

                                        <td> <small class= "text-white label label-primary">{{ucfirst($state['sendPINSMS'])}}</small></td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state['created_at'])) }}</td>
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
        url: '/delete/virtualrequest/' + id,
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
