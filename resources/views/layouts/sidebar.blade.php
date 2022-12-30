<aside class="main-sidebar">
    <!-- sidebar-->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="image">
          <img src="{{asset('img/admins.png')}}" class="img-circle" alt="User Image">
        </div>
        <div class="info">
          <p>{{ucfirst(Auth::User()->name)}}</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
          <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <!-- /.search form -->
      <!-- sidebar menu-->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        <li class="{{Request::is('dashboard') ? 'active' : '' }}">
          <a href="{{route('dashboard')}}">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
        <li class="treeview {{ Request::is('airtime')||Request::is('tv')||Request::is('power') || Request::is('data') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-lock"></i> <span> Utility Bills</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ Request::is('airtime') ? 'active' : '' }}"> <a href="{{route('cms.airtime')}}"><i class="fa fa-circle-o"></i> Airtime</a> </li>
              <li class="{{ Request::is('data') ? 'active' : '' }}"><a href="{{route('cms.data')}}"><i class="fa fa-circle-o"></i> Data </a></li>
              <li class="{{ Request::is('tv') ? 'active' : '' }}"><a href="{{route('cms.tv')}}"><i class="fa fa-circle-o"></i> TV </a></li>
              <li class="{{ Request::is('power') ? 'active' : '' }}"><a href="{{route('cms.power')}}"><i class="fa fa-circle-o"></i> Power </a></li>
            </ul>
          </li>
          <li class="treeview {{ Request::is('security/questions') || Request::is('passegers/otp') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-lock"></i> <span> Crypto</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ Request::is('security/questions') ? 'active' : '' }}"> <a href="{{route('cms.security')}}"><i class="fa fa-circle-o"></i> BTC</a> </li>
              <li class="{{ Request::is('passegers/otp') ? 'active' : '' }}"><a href=""><i class="fa fa-circle-o"></i> BNB </a></li>
              <li class="{{ Request::is('passegers/otp') ? 'active' : '' }}"><a href=""><i class="fa fa-circle-o"></i> ETH </a></li>
              <li class="{{ Request::is('passegers/otp') ? 'active' : '' }}"><a href=""><i class="fa fa-circle-o"></i> LITECOIN</a></li>
              <li class="{{ Request::is('passegers/otp') ? 'active' : '' }}"><a href=""><i class="fa fa-circle-o"></i> MATIC </a></li>
            </ul>
          </li>
        <li class="treeview {{ Request::is('all/users') || Request::is('users/referrals') || Request::is('users/this/month') || Request::is('consumer/followers')
        || Request::is('deleted/users') || Request::is('banned/users') || Request::is('users/registered/today') || Request::is('add/user')||
        Request::is('online/users') || Request::is('retailers/this/month') || Request::is('all/retailers') || Request::is('add/retailer') ? 'active open' : '' }}">
          <a href="#">
            <i class="fa fa-users"></i> <span>Users Manager</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ Request::is('all/users') ? 'active' : '' }}"><a href="{{route('cms.users')}}"><i class="fa fa-circle-o"></i> View all Users</a></li>
            <li class="{{ Request::is('users/registered/today') ? 'active' : '' }}"><a href="{{route('cms.users.today')}}"><i class="fa fa-circle-o"></i> Todays Registered Consumers</a></li>
            <li class="{{ Request::is('add/user') ? 'active' : '' }}"><a href="{{route('cms.add.user')}}"><i class="fa fa-circle-o"></i> Add New User </a></li>
            <li class="{{ Request::is('users/this/month') ? 'active' : '' }}"><a href="{{route('cms.thismonth.users')}}"><i class="fa fa-circle-o"></i> This Month Reg Users</a></li>
          </ul>
        </li>
        <li class="treeview {{ Request::is('all/messages') || Request::is('unread/messages') || Request::is('read/messages') ? 'active open' : '' }}">
          <a href="#">
            <i class="fa fa-envelope"></i> <span>Messages</span>
            <span class="pull-right-container">
              <small class="label pull-right bg-success">{{$readmsgcount}}</small>
              <small class="label pull-right bg-danger">{{$unreadcount}}</small>
            </span>
          </a>

          <ul class="treeview-menu">
            <li class="{{ Request::is('unread/messages') ? 'active' : '' }}"> <a href="{{route('cms.message.unread')}}"><i class="fa fa-circle-o"></i> Unread Messages  </a> </li>
            <li class="{{ Request::is('read/messages') ? 'active' : '' }}"> <a href="{{route('cms.read.messages')}}"><i class="fa fa-circle-o"></i> Read Messages </a> </li>
            <li class="{{ Request::is('all/messages') ? 'active' : '' }}"> <a href="{{route('cms.messages')}}"><i class="fa fa-circle-o"></i> All Messages</a> </li>
          </ul>
        </li>

        <li class="treeview {{ Request::is('security/questions') || Request::is('user/otp') ? 'active open' : '' }}">
          <a href="#">
            <i class="fa fa-lock"></i> <span> Authentication</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ Request::is('security/questions') ? 'active' : '' }}"> <a href="{{route('cms.security')}}"><i class="fa fa-circle-o"></i> Security Questions</a> </li>
            <li class="{{ Request::is('user/otp') ? 'active' : '' }}"><a href="{{route('cms.otp')}}"><i class="fa fa-circle-o"></i> OTP verification</a></li>
          </ul>
        </li>

        <li class="treeview {{ Request::is('users/index') || Request::is('all/roles') ? 'active open' : '' }}">
          <a href="#">
            <i class="fa fa-user-secret"></i> <span> Admin User Setup</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ Request::is('users/index') ? 'active' : '' }}"> <a href="{{route('cms.users.index')}}"><i class="fa fa-circle-o"></i> All Admins </a> </li>
            <li class="{{ Request::is('all/roles') ? 'active' : '' }}"> <a href="{{route('cms.users.roles')}}"><i class="fa fa-circle-o"></i> All Roles</a> </li>
          </ul>
        </li>

        <li class="{{ Request::is('site/settings') ? 'active' : '' }}">
          <a href="{{route('cms.settings')}}">
            <i class="fa fa-refresh"></i> <span> Site Settings</span>
          </a>
        </li>
        <li class="{{ Request::is('user-activities') ? 'active' : '' }}">
            <a href="{{route('cms.user.activities')}}">
              <i class="fa fa-refresh"></i> <span> User Activities</span>
            </a>
          </li>
        <li class="{{ Request::is('profile') ? 'active' : '' }}">
          <a href="{{route('cms.admin.profile')}}">
            <i class="fa fa-user"></i> <span> Profile</span>
          </a>
        </li>
        <li >
          <a href="{{route('logout')}}">
            <i class="fa fa-power-off"></i> <span> Logout</span>
          </a>
        </li>
          </ul>
        </li>

      </ul>
    </section>
    <!-- /.sidebar -->
    <div class="sidebar-footer">
		<!-- item-->
		<a href="{{route('cms.settings')}}" class="link" data-toggle="tooltip" title="" data-original-title="Settings"><i class="fa fa-cog fa-spin"></i></a>
		<!-- item-->
		<a href="{{route('cms.messages')}}" class="link" data-toggle="tooltip" title="" data-original-title="Email"><i class="fa fa-envelope"></i></a>
		<!-- item-->
		<a href="{{route('logout')}}" class="link" data-toggle="tooltip" title="" data-original-title="Logout"><i class="fa fa-power-off"></i></a>
	</div>
  </aside>
