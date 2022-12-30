@extends('layouts.app')
@section('title')
    All Giftcard Customers
@endsection
@section('content')


<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-warning">
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="header">
                        <h2> Gift Card Customers</h2>
                    </div>
                        {{-- <h4 class="box-title">  <small class="label label-info position-right">Total Passengers: {{ \App\Models\User::all()->where('account_type', '["passenger"]')->count() }}</small></h4> --}}
                        <h4 class="box-title">  <small class="label label-info position-right">Total Giftcard Customers: {{ $pasengercnt }}</small></h4>
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
                                        <th>Given Name</th>
                                        <th>Family Name </th>
                                        <th>Email </th>
                                        <th>Address 1 </th>
                                        <th>Address 2</th>
                                        <th> Locality</th>
                                        <th> Administrative District Level 1</th>
                                        <th> Postal Code</th>
                                        <th> Country</th>
                                        <th> PhoneNumber</th>
                                        <th> Reference ID</th>
                                        <th>  Note</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Customer</th>
                                        <th>Given Name</th>
                                        <th>Family Name </th>
                                        <th>Email </th>
                                        <th>Address 1 </th>
                                        <th>Address 2</th>
                                        <th> Locality</th>
                                        <th> Administrative District Level 1</th>
                                        <th> Postal Code</th>
                                        <th> Country</th>
                                        <th> PhoneNumber</th>
                                        <th> Reference ID</th>
                                        <th>  Note</th>
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
                                        <td>{{ucfirst($state['given_name'])}}</td>
                                        <td>{{ucfirst($state['family_name'])}}</td>
                                        <td>{{ucfirst($state['email_address'])}}</td>
                                        <td>{!! $state['address_line_1'] !!}</td>
                                        <td>{!! $state['address_line_2'] !!}</td>
                                        <td>{{ucfirst($state['locality'])}}</td>
                                        <td>{{ucfirst($state['administrative_district_level_1'])}}</td>
                                        <td>{{ucfirst($state['postal_code'])}}</td>
                                        <td>{{ucfirst($state['country'])}}</td>
                                        <td>{{ucfirst($state['phone_number'])}}</td>
                                        <td>{{ucfirst($state['reference_id'])}}</td>
                                        <td>{!! $state['note'] !!}</td>
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
        url: '/delete/giftcardcustomer/' + id,
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
