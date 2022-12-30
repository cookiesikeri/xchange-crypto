@extends('layouts.app')
@section('title')
Edit Consumer
@endsection
@section('content')
<section class="content-header">
    <h1>
        Edit Consumer
    </h1>
    <ol class="breadcrumb">
       <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>

       <li class="active"> Edit Consumer</li>
    </ol>
 </section>
 <section class="content">
        <!-- Input -->
        <div class="row">
            <div class="col-md-12">

               <div class="box box-warning">
                  <!-- /.box-header -->
                  <div class="box-body">
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
                        <form  action="{{route('cms.consumer.update',$product->id)}}" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}
                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="form-line">
                                        <label>Retailer Name</label>
                                        <input type="text" class="form-control" id="size" name="name" value="{{ $product->name }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="form-line">
                                        <label>Email </label>
                                        <input type="email" class="form-control" id="size" name="email" value="{{ $product->email }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <select class="form-control show-tick" name="gender">
                                        <option value="{{ $product->gender }}">--{{ $product->gender }}--</option>
                                        <option value="" disabled>--Select gender--</option>
                                        <option value="Male"> Male</option>
                                        <option value="Female">Female </option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="number" class="form-control" placeholder="Phone Number" name="phone" value="{{ $product->phone }}">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Profile Picture</label>
                                        <input type="file" class="form-control" name="picture">
                                        @if ($errors->has('picture'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('picture') }}</strong>
                                        </span>
                                        @endif

                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="password" class="form-control"placeholder="Password" name="password" placeholder="*******">
                                        @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('password') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="password" class="form-control"placeholder="Confirm Password" name="password_confirmation" placeholder="*****">
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
