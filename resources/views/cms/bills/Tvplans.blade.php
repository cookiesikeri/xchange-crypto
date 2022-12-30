@extends('layouts.app')
@section('title')
TV Bundles
@endsection
@section('content')


<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                    <div class="body">
                        <div class="card">
                            <div class="header">
                                <h2> TV Banquets</h2>
                                <h4 class="box-title">  <small class="label label-info position-right">Total TV Bundles: {{ $pasengercnt }}</small></h4>
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
                                        <th>Name</th>
                                        <th>Variation ID</th>
                                        <th>Amount</th>
                                        <th>Availability</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Name</th>
                                        <th>Variation ID</th>
                                        <th>Amount</th>
                                        <th>Availability</th>
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
                                        <td>{{ucfirst($state->name)}}</td>
                                        <td> <small class= "text-white label label-primary">{{ucfirst($state['variation_id'])}}</small></td>
                                        <td>₦{{number_format($state->amount, 2)}}</td>
                                        <td> <small class= "text-white label label-success">YES</small></td>
                                        <td>
                                                <button type="button" onclick="deleteContact({{ $state->id }})" class="btn btn-danger pd-x-20" data-dismiss="modal"><i class="fa fa-trash"></i></button>
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
</section>

    <script>
        function deleteContact(id) {

    event.preventDefault();

    if (confirm("Are you sure?")) {

        $.ajax({
            url: 'delete/tvplans/' + id,
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
