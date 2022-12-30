@extends('layouts.app')
@section('title')
    This Month Registered users
@endsection
@section('content')

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-warning">
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="header">
                        <h2> This Month Registered users in the system </h2>
                        <button type="button" class="btn btn-primary waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal">Add New User</button>
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more-vert"></i> </a>
                                <ul class="dropdown-menu pull-right">
                                    <li><a href="">Refresh</a></li>
                                </ul>
                            </li>
                        </ul>
                        {{-- <h4 class="box-title">  <span class="label label-info position-right">Total Passengers: {{ \App\Models\User::all()->where('account_type', '["passenger"]')->count() }}</span></h4> --}}
                        <h4 class="box-title">  <span class="label label-info position-right">Total Users: {{ $pasengercnt }}</span></h4>
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

                                        <th>FullName</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Date Registered</th>
                                        <th> Photo</th>
                                        <th> Gender</th>
                                        <th> DOB</th>
                                        <th> Source</th>
                                        <th>Is Active</th>
                                        <th>Is Banned</th>
                                        <th>Banned Start Date</th>
                                        <th>Banned End Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>

                                        <th>FullName</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Date Registered</th>
                                        <th> Photo</th>
                                        <th> Gender</th>
                                        <th> DOB</th>
                                        <th> Source</th>
                                        <th>Is Active</th>
                                        <th>Is Banned</th>
                                        <th>Banned Start Date</th>
                                        <th>Banned End Date</th>
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

                                        <td>{{ucfirst($state['name'])}}</td>
                                        <td>{{$state['email']}}</td>
                                        <td>{{$state['phone']}}</td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state['created_at'])) }}</td>
                                        <td>
                                            @if ($state->picture != '')
                                            <a href="{{URL::to($state['picture'])}}" target="_blank"> <img src="{{URL::to($state['picture'])}}" height="50" width="50"></a>
                                            @else
                                        <img src="https://edekee-backend-main.s3.us-east-2.amazonaws.com/admin/img/user.png" height="50" width="50">
                                            @endif
                                        </td>
                                        <td>{{$state['gender']}}</td>
                                        <td>{{ date('M j, Y', strtotime($state['dob'])) }}</td>
                                        <td>
                                            @if ($state->source == 'admin')
                                            <small class= "text-white label label-success">Admin</small>
                                            @else

                                            <small class= "text-white label label-primary">Default</small>
                                            @endif

                                        </td>
                                        <td>
                                            @if($state['is_active'] == 1)
                                                <small class= "text-white label label-success">Online</small>
                                                @else
                                                <small class= "text-white label label-warning">Offline</small>
                                            @endif
                                            </td>
                                            <td>
                                                @if($state['is_banned'] == 1)
                                                <small class= "text-white label label-danger">Yes</small>
                                                @else
                                                <small class= "text-white label label-primary">No</small>
                                            @endif
                                            </td>
                                        </td>
                                        <td>
                                            @if($state['is_banned'] == '')
                                            <small></small>
                                            @else
                                            <small>{{ date('M j, Y', strtotime($state['ban_start_date'])) }}</small>
                                        @endif
                                        </td>
                                        <td>
                                            @if($state['is_banned'] == '')
                                            <small></small>
                                            @else
                                            <small>{{ date('M j, Y', strtotime($state['ban_end_date'])) }}</small>
                                        @endif
                                        </td>
                                        <td>
                                            <a href="{{route('cms.consumer.edit', ['id' => $state->id])}}">
                                            <button type="button" class="btn btn-primary"><i class="fa fa-edit"></i></button>
                                                </a>
                                                <a href="{{route('cms.consumer.delete', ['id' => $state->id])}}">
                                            <button type="button"  class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                            </a>

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
@include('modals.add_user')

@endsection
