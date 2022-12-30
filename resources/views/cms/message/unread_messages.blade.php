@extends('layouts.app')
@section('title')
Unread Messages
@endsection
@section('content')

<section class="content-header">
    <h1>
        Unread Messages ({{ $pasengercnt }})
    </h1>
    <ol class="breadcrumb">
       <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>
       <li><a href="#"> Unread Messages</a></li>
       <li class="active"> Unread Messages ({{ $pasengercnt }})</li>
    </ol>
 </section>
          <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                      <h3 class="box-title">All Unread Messages</h3>
                    </div>
                    <!-- /.box-header -->
                    @if(Session::has('danger'))
                    <div class="col-md-12">
                        <div class="alert alert-danger no-b">
                            <span class="text-semibold"></span> {{ Session::get('error')}}
                            <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                        </div>
                    </div>
                    @endif
                @if(Session::has('success'))
                <div class="col-md-12">
                    <div class="alert alert-success no-b">
                        <span class="text-semibold"></span> {{ Session::get('success')}}
                        <button type="button" class="close" data-dismiss="alert"><span>×</span></button>
                    </div>
                </div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div><br />
                @endif
                    <div class="box-body">
                      <table id="example" class="table table-bordered table-hover display nowrap margin-top-10">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>UserName</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>UserName</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Action</th>

                                    </tr>
                                </tfoot>
                                <tbody>
                                    @if(count($contacts) < 1)

                                    <tr>
                                        <th>No record currently available</th>
                                    </tr>

                                    @else
                                    @foreach($contacts as $key=>$state)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td><img src="https://edekee-backend-main.s3.us-east-2.amazonaws.com/admin/img/user.png" alt="profile img" class="user-image" height="50"> {{ucfirst($state->name)}}</td>
                                        <td>

                                                @if ($state->status ==1)
                                                <span class="label label-success">Read</span>
                                                @else
                                                <span class="label label-warning">Unread</span>
                                                @endif

                                        </td>
                                        <td> {!! Str::limit($state->subject ,100) !!}</td>
                                        <td> {{ date('M j, Y h:ia', strtotime($state['created_at'])) }} </td>
                                        <td>
                                            <a href="{{route('cms.message.details', $state->id)}}">
                                                <button type="button" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> </button>
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
    </section>


@endsection
