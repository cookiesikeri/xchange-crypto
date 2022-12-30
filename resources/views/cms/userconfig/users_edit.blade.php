@extends('layouts.app')
@section('title')
Edit User Account
@endsection
@section('content')
<section class="content">
    <div class="row">
      <div class="col-md-12">

        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Our Services
            </h3>
            <!-- tools box -->
            <div class="pull-right box-tools">
              <button type="button" class="btn btn-default btn-sm" data-widget="collapse" data-toggle="tooltip"
                      title="Collapse">
                <i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-default btn-sm" data-widget="remove" data-toggle="tooltip"
                      title="Remove">
                <i class="fa fa-times"></i></button>
            </div>
            <!-- /. tools -->
          </div>
          <!-- /.box-header -->
          <div class="box-body pad">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Edit Administrator </div>
                        <div class="elements">
                            <a class="btn btn-primary btn-sm" href="{{route('cms.users.index')}}">Go back</a>
                        </div>
                    </div>
                    <div class="card-block">
                    <div class="form-layout">
                            <div class="col-md-12">
                                <form action="{{route('cms.users.update')}}" method="post">
                                    {{csrf_field()}}
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="id" id="id" value="{{$admin->id}}">
                                    <div class="form-group mg-b-10-force">
                                        <label for="name" class="control-label">Name</label>
                                        <input type="text" name="name" id="name" class="form-control" value="{{$admin->name}}">
                                    </div>
                                    <div class="form-group mg-b-10-force">
                                        <label for="email" class="control-label">E-Mail</label>
                                        <input type="email" name="email" id="email" class="form-control" value="{{$admin->email}}">
                                    </div>
                                    <div class="form-group mg-b-10-force">
                                        <label for="role" class="control-label">Role</label>
                                        <select name="role_id" id="role_id" class="form-control">
                                            @forelse ($roles as $role)
                                                @if($role->id == $admin->id)
                                                <option selected value="{{$role->id}}">{{$role->title}}</option>
                                                @else
                                                <option value="{{$role->id}}">{{$role->title}}</option>
                                                @endif
                                            @empty
                                                <option value="0">No roles defined yet.</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary btn-sm">Update account</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <!-- /.col-->
    </div>
    <!-- ./row -->
    </section>
    <!-- /Custom header -->
@endsection
