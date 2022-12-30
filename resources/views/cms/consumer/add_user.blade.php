@extends('layouts.app')
@section('title')
Add User
@endsection
@section('content')
<section class="content-header">
    <h1>
        Create New User
    </h1>
    <ol class="breadcrumb">
       <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>
       <li class="active"> Create New User </li>
    </ol>
 </section>
        <!-- Input -->
        <section class="content">
            <!-- Input -->
            <div class="row">
                <div class="col-md-12">

                   <div class="box box-warning">
                      <!-- /.box-header -->
                      <div class="box-body">
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
                                <form  action="{{ route('cms.register.user') }}" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}
                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-7">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="name"  placeholder="Full Name"/>
                                        @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="email" class="form-control" placeholder="Email" name="email"/>
                                        @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('email') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <select class="form-control show-tick" name="sex">
                                        <option value="">--Select gender--</option>
                                        <option value="Male"> Male</option>
                                        <option value="Female">Female </option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="number" class="form-control" placeholder="Phone Number" name="phone"/>
                                        @if ($errors->has('phone'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('phone') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="password" class="form-control"placeholder="Password" name="password" />
                                        @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('password') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="password" class="form-control"placeholder="Confirm Password" name="password_confirmation" />
                                        @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('password') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary waves-effect m-r-20">SAVE CHANGES</button>
                        </div>
                        </form>
                    </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection
