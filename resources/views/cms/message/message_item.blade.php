@extends('layouts.app')
@section('title')
    {{$order->subject}}
@endsection
@section('content')



        <section class="content-header">
            <h1>
                All Services
            </h1>
            <ol class="breadcrumb">
              <li><a href="{{route('dashboard')}}"><i class="fa fa-dashboard"></i> Home</a></li>
              <li><a href="{{route('cms.message.unread')}}">Unread Messages</a></li>
              <li class="active"> {{$order->subject}} </li>
            </ol>
          </section>

          <section class="content">
            <!-- Input -->
            <div class="row">
                <div class="col-md-12">

                   <div class="box box-warning">
                      <!-- /.box-header -->
                      <div class="box-body">
                <div class="body mail-single">
                    <div class="row clearfix">
                        <div class="col-lg-12">
                            <a href="{{route('cms.messages.destroy', ['id' => $order->id])}}">
                                <button type="button"  class="btn btn-danger"> <i class="fa fa-trash"></i></button>
                            </a>
                        </div>
                        <div class="col-lg-12">
                            <h3 class="m-t-0">{{$order->subject}}</h3>
                            <div class="media">

                                <div class="media-body">
                                    <p class="m-b-0"> <span class="text-muted">from</span> <a href="javascript:;" class="text-default">{{$order->email}}</a> <span class="text-muted text-sm float-right">{{ date('M j, Y h:ia', strtotime($order['created_at'])) }}</span> </p>
                                    <p class="m-b-0"><span class="text-muted">to</span> Admin</p>

                                </div>
                            </div>
                            <hr>
                        </div>
                        <div class="col-lg-12">
                            <p>{!! $order->message !!}</p>

                            <hr>
                        </div>

                        <div class="col-lg-12">
                            <div class="panel-heading m-t-20">


                            <div class="m-b-10 m-t-10"> <a href="{{route('cms.message.markunread', ['id' => $order->id])}}">
                                <button type="button"  class="btn btn-warning"> <i class="fa fa-envelope"></i> Mark As Unread</button>
                            </a></a>
                             or
                            <a href="{{route('cms.message.markread', ['id' => $order->id])}}">
                                <button type="button"  class="btn btn-primary"> <i class="fa fa-envelope-open"></i> Mark As Read</button>
                            </a>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
