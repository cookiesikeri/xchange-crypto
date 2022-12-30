@extends('layouts.app')
@section('title')
Dashboard
@endsection
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        <small>Welcome Back! {{ucfirst(Auth::User()->name)}} </small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
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
    <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.users.today')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"><i class="fa fa-users"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Todays Registered Users">Todays Registered Users</span>
                 <span class="info-box-number">{{ \App\Models\User::where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\User::where('account_type_id', 1)->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>

     <!-- /.col -->
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.thismonth.users')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"><i class="fa fa-users"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="This Month Users">This Month Users</span>
                 <span class="info-box-number">{{ $new_user_regs_this_month}}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ $new_user_regs_this_month}}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <!-- /.col -->
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.users')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">    <i class="fa fa-users"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="total users">Total Users</span>
                 <span class="info-box-number">{{ \App\Models\User::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\User::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.user.activities')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"> <i class="fa fa-refresh"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="User Activities">User Activities</span>
                 <span class="info-box-number">{{ \App\Models\UserActivity::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\UserActivity::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.message.unread')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-envelope"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Unread Messages"> Unread Messages</span>
                 <span class="info-box-number">{{ \App\Models\ContactMessage::where('is_treated', 0)->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\ContactMessage::where('is_treated', 0)->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <!-- /.col -->
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.messages')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-envelope-o"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Total Messages"> Total Messages </span>
                 <span class="info-box-number">{{ \App\Models\ContactMessage::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\ContactMessage::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="#">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-bitcoin"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="bitcoin addresses "> BTC Addresses </span>
                 <span class="info-box-number">{{ \App\Models\BitconWallet::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\BitconWallet::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="#">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-bitcoin"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Etherum addresses "> ETH Addresses </span>
                 <span class="info-box-number">{{ \App\Models\EtherumWalletAdress::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\EtherumWalletAdress::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="#">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-bitcoin"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="litecoin addresses "> LITeCOIN Addresses </span>
                 <span class="info-box-number">{{ \App\Models\LitecoinWalletAddress::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\LitecoinWalletAddress::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="#">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-bitcoin"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="polygon Addresses"> POLYGON Addresses </span>
                 <span class="info-box-number">{{ \App\Models\PolygonWalletAddress::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\PolygonWalletAddress::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="#">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-bitcoin"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="dogecoin Addresses"> DOGE Addresses </span>
                 <span class="info-box-number">{{ \App\Models\DogeCoinWalletAddress::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\DogeCoinWalletAddress::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="#">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple">   <i class="fa fa-bitcoin"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="binance Addresses"> BNB Addresses </span>
                 <span class="info-box-number">{{ \App\Models\BinanceWallet::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\BinanceWallet::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.user.activities')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"> <i class="fa fa-telegram"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Airtime Transactions">Airtime Transactions</span>
                 <span class="info-box-number">{{ \App\Models\AirtimeTransaction::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\AirtimeTransaction::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.user.activities')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"> <i class="fa fa-mobile"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Data Transactions">Data Transactions</span>
                 <span class="info-box-number">{{ \App\Models\DataTransaction::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\DataTransaction::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.user.activities')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"> <i class="fa fa-television"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="TV Transactions">TV Transactions</span>
                 <span class="info-box-number">{{ \App\Models\TVTransaction::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\TVTransaction::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>
     <div class="col-md-3 col-sm-6 col-xs-12">
        <a href="{{route('cms.user.activities')}}">
           <div class="info-box">
              <span class="info-box-icon push-bottom bg-purple"> <i class="fa fa-lightbulb-o"></i></span>
              <div class="info-box-content">
                 <span class="info-box-text" title="Power Transactions">Power Transactions</span>
                 <span class="info-box-number">{{ \App\Models\PowerTransaction::all()->count() }}</span>
                 <div class="progress">
                    <div class="progress-bar progress-bar-primary" style="width: {{ \App\Models\PowerTransaction::all()->count() }}%"></div>
                 </div>
              </div>
              <!-- /.info-box-content -->
           </div>
        </a>
        <!-- /.info-box -->
     </div>

    </div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
          <div class="col-md-6">
              <div class="box box-solid">
                  <div class="box-header with-border">
                    <h3 class="box-title">Utility Bills</h3>
                  </div>
                  <!-- /.box-header -->
                  <div class="box-body">
                    <p>Airtime</p>

                    <div class="progress">
                      <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="{{ $order_this_month }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $order_this_month }}%">
                        <span class="sr-only">{{ $order_this_month }}%</span>
                      </div>
                    </div>
                    <p>Data</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="{{ $service_this_month }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $service_this_month }}%">
                        <span class="sr-only">{{ $order_this_month }}% </span>
                      </div>
                    </div>
                    <p>TV</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="{{ $products_this_month }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $products_this_month }}%">
                        <span class="sr-only">{{ $products_this_month }}% </span>
                      </div>
                    </div>
                    <p>Power</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="{{ $ordercomplaint_this_month }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $ordercomplaint_this_month }}0%">
                        <span class="sr-only">{{ $ordercomplaint_this_month }}%</span>
                      </div>
                    </div>
                  </div>
                  <!-- /.box-body -->
                </div>
            <!-- /.chat -->
          </div>
          <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                  <h3 class="box-title">Crypto Transactions</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <p>BITCOIN</p>

                    <div class="progress">
                      <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="{{ $bit }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $bit }}%">
                        <span class="sr-only">{{ $bit }}%</span>
                      </div>
                    </div>
                    <p>ETHERUM</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="{{ $eth }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $eth }}%">
                        <span class="sr-only">{{ $eth }}% </span>
                      </div>
                    </div>
                    <p>LITECOIN</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="{{ $lit }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $lit }}%">
                        <span class="sr-only">{{ $lit }}% </span>
                      </div>
                    </div>
                    <p>BNB</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="{{ $bnb }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $bnb }}0%">
                        <span class="sr-only">{{ $bnb }}%</span>
                      </div>
                    </div>
                    <p>DOGECOIN</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="{{ $dog }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $dog }}0%">
                        <span class="sr-only">{{ $dog }}%</span>
                      </div>
                    </div>
                    <p>POLYGON</p>

                    <div class="progress">
                        <div class="progress-bar progress-bar-primary progress-bar-striped" role="progressbar" aria-valuenow="{{ $pol }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ $pol }}0%">
                        <span class="sr-only">{{ $pol }}%</span>
                      </div>
                    </div>
                </div>
                <!-- /.box-body -->
              </div>
          <!-- /.chat -->
        </div>
          <!-- /.box (chat box) -->

      </div>

        </section>
        <section class="col-lg-12 connectedSortable">
            <div class="box">
                <div class="box-header">
                  <h3 class="box-title">All Registered Users</h3>
                  <h6 class="box-subtitle"></h6>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example" class="table table-bordered table-hover display nowrap margin-top-10">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>FullName</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date Registered</th>
                            <th> Photo</th>
                            <th> Gender</th>
                            <th> DOB</th>
                            <th> BVN</th>
                            <th>Is Active</th>
                            <th>Is Banned</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($users) < 1)
                        <tr>
                           <th colspan="9">No record currently available</th>
                        </tr>
                        @else
                        @foreach($users as $key=>$state)
                        <tr>
                           <td>{{++$key}}</td>
                           <td>{{ucfirst($state['name'])}}</td>
                           <td>{{$state['email']}}</td>
                           <td>{{$state['phone']}}</td>
                           <td>{{ date('M j, Y h:ia', strtotime($state['created_at'])) }}</td>
                           <td>
                              @if ($state->picture != '')
                              <a href="{{URL::to($state['picture'])}}" target="_blank"> <img src="{{URL::to($state['picture'])}}" height="50" width="50"></a>
                              @else
                              <img src="{{asset('img/admins.png')}}" height="50" width="50">
                              @endif
                           </td>
                           <td>{{$state['gender']}}</td>
                           <td>{{ date('M j, Y', strtotime($state['dob'])) }}</td>
                           <td>{{$state['bvn']}}</td>
                           <td>
                            <small class="label label-success">Online</small>
                         </td>
                         <td>
                            <small class="label label-primary">No</small>
                         </td>
                         </td>
                         <td>
                            <a href="{{route('cms.consumer.edit', ['id' => $state->id])}}">
                            <button type="button" class="btn btn-block btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> </button>
                            </a>
                            <a href="{{route('cms.consumer.delete', ['id' => $state->id])}}">
                            <button type="button" class="btn btn-block btn-danger"><i class="fa fa-trash" aria-hidden="true"></i> </button>
                            </a>
                         </td>
                        <tr>
                            @endforeach
                            @endif
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>S/N</th>
                            <th>FullName</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date Registered</th>
                            <th> Photo</th>
                            <th> Gender</th>
                            <th> DOB</th>
                            <th> BVN</th>
                            <th>Is Active</th>
                            <th>Is Banned</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
                </div>
        </section>
        <!-- /.Left col -->
        <!-- right col (We are only adding the ID to make the widgets sortable)-->
        <section class="col-lg-12 connectedSortable">
            <div class="box">
                <div class="box-header">
                  <h3 class="box-title">Wallet Transactions Table</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Wallet ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Sender Name</th>
                            <th>Sender Acc</th>
                            <th>Ref</th>
                            <th>Bank Name</th>
                            <th>Receiver Acc</th>
                            <th>Receiver Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Transaction Type</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($trans) < 1)
                        <tr>
                           <th colspan="9">No record currently available</th>
                        </tr>
                        @else
                        @foreach($trans as $key=>$state)
                        <tr>
                            <td>{{++$key}}</td>
                            <td>{{ucWords(App\Models\Wallet::find($state->wallet_id)->id)}}</td>
                            <td><small class="label label-primary">{{$state->type}}</small></td>
                            <td>₦{{number_format($state->amount, 2)}}</td>
                            <td>{{ucfirst($state['sender_name'])}}</td>
                            <td>{{ucfirst($state['sender_account_number'])}}</td>
                            <td><code> {{ucfirst($state['reference'])}}</code></td>
                            <td>{{ucfirst($state['receiver_account_number'])}}</td>
                            <td>{{ucfirst($state['receiver_name'])}}</td>
                            <td>{{ucfirst($state['bank_name'])}}</td>
                            <td>{!! $state->description !!}</td>
                            <td>
                                @if(strtolower($state->status) == "success")
                                <small class="label label-success">Success</small>
                                @elseif(strtolower($state->status) == "pending")
                                <small class="label label-warning">Pending</small>
                                @else
                                <small class="label label-danger">Failed</small>
                                @endif
                            </td>
                            <td>
                                @if(strtolower($state->transaction_type) == "wallet")
                                <small class="label label-success">Success</small>
                                @elseif(strtolower($state->transaction_type) == "bank")
                                <small class="label label-warning">Bank</small>
                                @else
                                <small class="label label-danger">Card</small>
                                @endif
                            </td>
                            <td>{{ date('M j, Y h:ia', strtotime($state['created_at'])) }}</td>
                        </tr>


                        @endforeach
                        @endif

                    </tbody>

                    <tfoot>
                        <tr>
                            <th>S/N</th>
                            <th>Wallet ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Sender Name</th>
                            <th>Sender Acc</th>
                            <th>Ref</th>
                            <th>Bank Name</th>
                            <th>Receiver Acc</th>
                            <th>Receiver Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Transaction Type</th>
                            <th>Time</th>
                        </tr>
                    </tfoot>
                </table>
        </section>
        <!-- right col -->
      </div>
      <!-- /.row (main row) -->

    </section>
    <!-- /.content -->

@endsection
