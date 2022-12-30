@extends('layouts.app')
@section('title')
Update user Wallet
@endsection
@section('content')
<section class="content-header">
    <h1>
        Update user Wallet
    </h1>
    <ol class="breadcrumb">
       <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>
       <li class="active"> Update user Wallet </li>
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
                            <form  action="{{route('cms.wallet.update',$product->id)}}" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}

                        </div>
                        <div class="row clearfix">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="form-line">
                                        <input type="number" class="form-control" name="balance"  value="{{ $product->balance }}"/>
                                        @if ($errors->has('balance'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong class="text-danger">{{ $errors->first('balance') }}</strong>
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
