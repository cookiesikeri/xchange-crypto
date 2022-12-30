@extends('layouts.app')
@section('title')
    Security Questions
@endsection
@section('content')


<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                    <div class="body">
                        <div class="card">
                            <div class="header">
                                <h2> security/questions</h2>
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
                                        <th>Question</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Question</th>
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
                                        <td>{{ucfirst($state->question)}}</td>
                                        <td>
                                            <a onclick="fetchPost({{ $state->id }})">
                                                <button type="button" class="btn btn-primary waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal2"><i class="zmdi zmdi-edit"></i></button>
                                              </a>
                                                <button type="button" onclick="deleteContact({{ $state->id }})" class="btn btn-danger pd-x-20" data-dismiss="modal"><i class="zmdi zmdi-delete"></i></button>
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

    function fetchPost(id) {

    event.preventDefault();

    $.ajax({
    url: 'service/' + id,
    method: 'get',
    success: function(result){
        console.log(result);
        $('#titleEdit').val(result.name);
        $('#contentEdit').val(result.body);
        var url = 'service/' + id;
        $('form#service').attr('action', url);
        $('#editserviceModal').modal('show');
    }
    });

    }
    </script>
    <script>
        function deleteContact(id) {

    event.preventDefault();

    if (confirm("Are you sure?")) {

        $.ajax({
            url: 'delete/securityquestion/' + id,
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
