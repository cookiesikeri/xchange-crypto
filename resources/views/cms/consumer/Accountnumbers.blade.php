@extends('layouts.app')
@section('title')
Account Numbers
@endsection
@section('content')


<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">

                    <div class="body">
                        <div class="card">
                            <div class="header">
                                <h2> All Users Account Numbers</h2>
                                <h4 class="box-title">  <small class="label label-info position-right">Total Account Numbers: {{ $pasengercnt }}</small></h4>
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
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>Wallet ID</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>Wallet ID</th>
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
                                        <td>{{ucfirst($state->account_name)}}</td>
                                        <td>{{ucfirst($state->account_number)}}</td>
                                        <td>{{ucWords(App\Models\Wallet::find($state->wallet_id)->id)}}</td>
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
