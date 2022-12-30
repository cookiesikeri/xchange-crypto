<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{$site->site_name}}">
    <link rel="icon" href="{{URL::to($site->logo)}}">

    <title>{{$site->site_name}} | Admin Login Portal</title>
    @include('layouts.css')

</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    {{-- <img src="{{URL::to($site->logo)}}" height="100" width="100"> --}}
    <img src="{{URL::to($site->logo)}}" height="100" width="100">
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">{{$site->site_name}} Admin Login Portal</p>
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
    <form action="{{ route('login.submit') }}" method="post" class="form-element">
        @csrf
      <div class="form-group has-feedback">
        <input type="email" class="form-control" placeholder="Email" name="email" required>
        <span class="ion ion-email form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" required>
        <span class="ion ion-locked form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-6">
          <div class="checkbox">
            <input type="checkbox" id="basic_checkbox_1" name="remember">
			<label for="basic_checkbox_1">Remember Me</label>
          </div>
        </div>

        <!-- /.col -->
        <div class="col-xs-12 text-center">
          <button type="submit" class="btn btn-info btn-block btn-flat margin-top-10">SIGN IN</button>
        </div>
        <!-- /.col -->
      </div>
    </form>


  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

@include('layouts.js')

</body>
</html>
