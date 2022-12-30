@extends('layouts.app')
@section('title')
All Roles
@endsection
@section('content')

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
                    <div class="header">
                        <h2>  All Roles </h2>
                        <button type="button" class="btn btn-primary waves-effect m-r-20" data-toggle="modal" data-target="#defaultModal">Add New Role</button>
>
                        </ul>
                        <h4 class="box-title">  <span class="badge badge-primary position-right">Total Admin Roles: {{count($roles)}}</span></h4>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover dataTable js-exportable" id="example">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Title</th>
                                        <th>Users Count</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Title</th>
                                        <th>Users Count</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    @if(count($roles) < 1)

                                    <tr>
                                        <th>No record currently available</th>
                                    </tr>

                                    @else
                                    @foreach($roles as $key=>$state)
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <th>{{$state->title}}</th>
                                        <th>{{count($state->admins)}}</th>
                                        <td>

                                            <button type="button" onclick="deleteContact({{ $state->id }})" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-trash"></i></button>
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
    </div>

</section>
@include('modals.create_new_roles')

    <script>
        function deleteContact(id) {

    event.preventDefault();

    if (confirm("Are you sure?")) {

        $.ajax({
            url: '/backend/delete/roles/' + id,
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
