@extends('layouts.app')
@section('title')
    All Virtual Accounts
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
                        <h4 class="box-title">  <small class="label label-info position-right">Total Virtual Accounts: {{ $pasengercnt }}</small></h4>
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
                                        <th>Name</th>
                                        <th>Acc Number</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Other Name</th>
                                        <th> DOB</th>
                                        <th> Line 1</th>
                                        <th> Line 2</th>
                                        <th> Status</th>
                                        <th> City</th>
                                        <th> State</th>
                                        <th> Postal Code</th>
                                        <th>Country</th>
                                        <th> Email Add</th>
                                        <th>Phone Number</th>
                                        <th>ID Type</th>
                                        <th>User Type</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Customer</th>
                                        <th>Name</th>
                                        <th>Acc Number</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Other Name</th>
                                        <th> DOB</th>
                                        <th> Line 1</th>
                                        <th> Line 2</th>
                                        <th> Status</th>
                                        <th> City</th>
                                        <th> State</th>
                                        <th> Postal Code</th>
                                        <th>Country</th>
                                        <th> Email Add</th>
                                        <th>Phone Number</th>
                                        <th>ID Type</th>
                                        <th>User Type</th>
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
                                        <td>{{ucfirst($state['name'])}}</td>
                                        <td>{{ucfirst($state['number'])}}</td>
                                        <td>{{ucfirst($state['firstName'])}}</td>
                                        <td>{{ucfirst($state['lastName'])}}</td>
                                        <td>{{ucfirst($state['otherNames'])}}</td>
                                        <td>{{ date('M j, Y', strtotime($state['dob'])) }}</td>
                                        <td>{{ucfirst($state['line1'])}}</td>
                                        <td>{{ucfirst($state['line2'])}}</td>
                                        <td> <small class= "text-white label label-success">{{ucfirst($state['status'])}}</small></td>
                                        <td>{{ucfirst($state['city'])}}</td>
                                        <td>{{ucfirst($state['state'])}}</td>
                                        <td>{{ucfirst($state['postalCode'])}}</td>
                                        <td>{{ucfirst($state['country'])}}</td>
                                        <td>{{ucfirst($state['emailAddress'])}}</td>
                                        <td>{{ucfirst($state['phoneNumber'])}}</td>
                                        <td> <small class= "text-white label label-primary">{{ucfirst($state['id_type'])}}</small></td>
                                        <td> <small class= "text-white label label-primary">{{ucfirst($state['user_type'])}}</small></td>

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
        url: '/delete/virtualaccount/' + id,
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
