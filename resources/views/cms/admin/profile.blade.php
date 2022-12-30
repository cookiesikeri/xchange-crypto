@extends('layouts.app')
@section('title')
    Profile
@endsection
@section('content')

<section class="content-header">
    <h1>
        My Profile
    </h1>
    <ol class="breadcrumb">
       <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>
       <li><a href="#"> My Profile</a></li>
       <li class="active"> {{ucfirst(Auth::User()->name)}}Profile</li>
    </ol>
 </section>

    <!-- Main content -->
    <section class="content">

      <div class="row">
        <div class="col-md-4">

          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="{{ucfirst(Auth::User()->image)}}" alt="User profile picture">

              <h3 class="profile-username text-center"> {{ucfirst(Auth::User()->name)}}</h3>

              <p class="text-muted text-center">{{ucfirst(Auth::User()->emaiil)}}</p>


              <div class="row">
              	<div class="col-xs-12">
              		<div class="profile-user-info">
						<p>Email address </p>
						<h5>{{ucfirst(Auth::User()->email)}}</h5>
						<p>FullName</p>
						<h5>{{ucfirst(Auth::User()->name)}}</h5>

					</div>
             	</div>
              </div>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
        <div class="col-md-8">
          <div class="nav-tabs-custom">
            <div class="tab-content">

             <div class="active tab-pane" id="activity">
                @if(Session::has('error'))
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
              <div class="tab-pane">
                <form class="form-horizontal form-element" method="POST" action="{{route('cms.admin.update', Auth::id() )}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group">
                    <label for="inputName" class="col-sm-2 control-label">Name</label>

                    <div class="col-sm-10">
                        <input type="text" placeholder="Full Name" class="form-control" name="name" value=" {{ucfirst(Auth::User()->name)}} ">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                        <input type="email" class="form-control" name="email" value=" {{ucfirst(Auth::User()->email)}}" placeholder="Email">
                    </div>
                  </div>


                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </div>
                </form>
              </div>
              <div class="tab-pane" id="settings">
                <form class="form-horizontal form-element" method="POST" action="{{route('admin.update.password', Auth::id() )}}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                  <div class="form-group">
                    <label for="inputName" class="col-sm-2 control-label">Current Password</label>

                    <div class="col-sm-10">
                        <input class="form-control" name="old_password" type="password" placeholder="*********" required="">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="col-sm-2 control-label">New Password</label>

                    <div class="col-sm-10">
                        <input class="form-control" name="new_password" type="password" required="">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputPhone" class="col-sm-2 control-label">Confirm New Password</label>

                    <div class="col-sm-10">
                        <input class="form-control" name="password_confirmation" type="password" required="">
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </div>
                </form>
              </div>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->

    </section>
    <!-- /.content -->
    @endsection
