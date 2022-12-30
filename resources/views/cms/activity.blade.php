@extends('layouts.app')
@section('title')
User Activities
@endsection
@section('content')
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover dataTable js-exportable" id="example">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>UserName</th>
                                        <th>Platform</th>
                                        <th>Activity</th>
                                        <th>Type</th>
                                        <th>City</th>
                                        <th>Region</th>
                                        <th>Country</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Status</th>
                                        <th>Date/Time</th>

                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>UserName</th>
                                        <th>Platform</th>
                                        <th>Activity</th>
                                        <th>Type</th>
                                        <th>City</th>
                                        <th>Region</th>
                                        <th>Country</th>
                                        <th>Latitude</th>
                                        <th>Longitude</th>
                                        <th>Status</th>
                                        <th>Date/Time</th>
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
                                        <td>{{ucWords(App\Models\User::find($state->user_id)->name)}}</td>
                                        <td>{{ucfirst($state->platform)}}</td>
                                        <td>{{ucfirst($state->activity)}}</td>
                                        <td>{{ucfirst($state->type)}}</td>
                                        <td>{{ucfirst($state->city)}}</td>
                                        <td>{{ucfirst($state->region)}}</td>
                                        <td>{{ucfirst($state->country)}}</td>
                                        <td>{{ucfirst($state->latitude)}}</td>
                                        <td>{{ucfirst($state->longitude)}}</td>
                                        <td>
                                            @if ($state->status >= 0)
                                            <small class= "label label-success">Active</small>
                                            @else

                                            <small class= "label label-danger">Disabled</small>
                                            @endif

                                        </td>
                                        <td>{{ date('M j, Y h:ia', strtotime($state->created_at)) }}</td>
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


@endsection
