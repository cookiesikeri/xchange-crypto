@extends('layouts.app')
@section('title')
     OTP verification
@endsection
@section('content')

                    <section class="content">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="box">

                                        <div class="body">
                                            <div class="card">
                                                <div class="header">
                                                    <h2>  OTP verification</h2>
                                                    <ul class="header-dropdown">
                                                        <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more-vert"></i> </a>
                                                            <ul class="dropdown-menu pull-right">
                                                                <li><a href="">Refresh</a></li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-hover dataTable js-exportable" id="example">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>OTP</th>
                                        <th>User ID</th>
                                        <th>Created Time</th>
                                        <th>Expires Time</th>

                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>OTP</th>
                                        <th>User ID</th>
                                        <th>Created Time</th>
                                        <th>Expires Time</th>

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
                                        <td>{{$state->otp}}</td>
                                        <td>{{$state->user_id}}</td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state->created_at)) }}</td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state->expires_at)) }}</td>
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
</section>


@endsection
