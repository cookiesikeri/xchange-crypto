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
        <li class="treeview {{ Request::is('airtime')||Request::is('tv')||Request::is('power') || Request::is('data/bundles') || Request::is('tv/bundles') || Request::is('electricity/disco') || Request::is('data') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-clone"></i> <span> Utility Bills</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ Request::is('airtime') ? 'active' : '' }}"> <a href="{{route('cms.airtime')}}"><i class="fa fa-circle-o"></i> Airtime</a> </li>
              <li class="{{ Request::is('data') ? 'active' : '' }}"><a href="{{route('cms.data')}}"><i class="fa fa-circle-o"></i> Data </a></li>
              <li class="{{ Request::is('tv') ? 'active' : '' }}"><a href="{{route('cms.tv')}}"><i class="fa fa-circle-o"></i> TV </a></li>
              <li class="{{ Request::is('power') ? 'active' : '' }}"><a href="{{route('cms.power')}}"><i class="fa fa-circle-o"></i> Electricity </a></li>
              <li class="{{ Request::is('data/bundles') ? 'active' : '' }}"><a href="{{route('cms.data.plans')}}"><i class="fa fa-circle-o"></i> Data Bundles </a></li>
              <li class="{{ Request::is('tv/bundles') ? 'active' : '' }}"><a href="{{route('cms.tv.plans')}}"><i class="fa fa-circle-o"></i> TV Bundles </a></li>
              <li class="{{ Request::is('electricity/discos') ? 'active' : '' }}"><a href="{{route('cms.power.plans')}}"><i class="fa fa-circle-o"></i> Electricity Discos </a></li>

            </ul>
          </li>
          <li class="treeview {{ Request::is('btc/wallets') || Request::is('dogecoin/wallets')  || Request::is('polygon/wallets') || Request::is('eth/wallets') || Request::is('litecoin/wallets') || Request::is('bnb/wallets') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-bitcoin"></i> <span> Crypto Wallets</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ Request::is('btc/wallets') ? 'active' : '' }}"> <a href="{{route('cms.btc.wallets')}}"><i class="fa fa-circle-o"></i> BTC</a> </li>
              <li class="{{ Request::is('bnb/wallets') ? 'active' : '' }}"><a href="{{route('cms.bnb.wallets')}}"><i class="fa fa-circle-o"></i> BNB </a></li>
              <li class="{{ Request::is('eth/wallets') ? 'active' : '' }}"><a href="{{route('cms.eth.wallets')}}"><i class="fa fa-circle-o"></i> ETH </a></li>
              <li class="{{ Request::is('litecoin/wallets') ? 'active' : '' }}"><a href="{{route('cms.ltc.wallets')}}"><i class="fa fa-circle-o"></i> LITECOIN</a></li>
              <li class="{{ Request::is('polygon/wallets') ? 'active' : '' }}"><a href="{{route('cms.pol.wallets')}}"><i class="fa fa-circle-o"></i> POLYGON </a></li>
              <li class="{{ Request::is('dogecoin/wallets') ? 'active' : '' }}"><a href="{{route('cms.dog.wallets')}}"><i class="fa fa-circle-o"></i> DOGECOIN </a></li>
            </ul>
          </li>
          <li class="treeview {{ Request::is('btc/transactions') || Request::is('dogecoin/transactions')  || Request::is('polygon/transactions') || Request::is('eth/transactions') || Request::is('litecoin/transactions') || Request::is('bnb/transactions') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-bitcoin"></i> <span> Crypto Transactions</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
                <li class="{{ Request::is('btc/transactions') ? 'active' : '' }}"> <a href="{{route('cms.btc.transactions')}}"><i class="fa fa-circle-o"></i> BTC</a> </li>
                <li class="{{ Request::is('bnb/transactions') ? 'active' : '' }}"><a href="{{route('cms.bnb.transactions')}}"><i class="fa fa-circle-o"></i> BNB </a></li>
                <li class="{{ Request::is('eth/transactions') ? 'active' : '' }}"><a href="{{route('cms.eth.transactions')}}"><i class="fa fa-circle-o"></i> ETH </a></li>
                <li class="{{ Request::is('litecoin/transactions') ? 'active' : '' }}"><a href="{{route('cms.ltc.transactions')}}"><i class="fa fa-circle-o"></i> LITECOIN</a></li>
                <li class="{{ Request::is('polygon/transactions') ? 'active' : '' }}"><a href="{{route('cms.pol.transactions')}}"><i class="fa fa-circle-o"></i> POLYGON </a></li>
                <li class="{{ Request::is('dogecoin/transactions') ? 'active' : '' }}"><a href="{{route('cms.dog.transactions')}}"><i class="fa fa-circle-o"></i> DOGECOIN </a></li>
            </ul>
          </li>
          <li class="treeview {{ Request::is('btc/mnemonic') || Request::is('dogecoin/mnemonic')  || Request::is('polygon/mnemonic') || Request::is('eth/mnemonic') || Request::is('litecoin/mnemonic') || Request::is('bnb/mnemonic') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-bitcoin"></i> <span> Crypto Mnemonic Keys</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
                <li class="{{ Request::is('btc/mnemonic') ? 'active' : '' }}"> <a href="{{route('cms.btc.mnemonic')}}"><i class="fa fa-circle-o"></i> BTC</a> </li>
                <li class="{{ Request::is('eth/mnemonic') ? 'active' : '' }}"><a href="{{route('cms.eth.mnemonic')}}"><i class="fa fa-circle-o"></i> ETH </a></li>
                <li class="{{ Request::is('litecoin/mnemonic') ? 'active' : '' }}"><a href="{{route('cms.ltc.mnemonic')}}"><i class="fa fa-circle-o"></i> LITECOIN</a></li>
                <li class="{{ Request::is('polygon/mnemonic') ? 'active' : '' }}"><a href="{{route('cms.pol.mnemonic')}}"><i class="fa fa-circle-o"></i> POLYGON </a></li>
                <li class="{{ Request::is('dogecoin/mnemonic') ? 'active' : '' }}"><a href="{{route('cms.dog.mnemonic')}}"><i class="fa fa-circle-o"></i> DOGECOIN </a></li>
            </ul>
          </li>
          <li class="treeview {{ Request::is('btc/keys') || Request::is('dogecoin/keys')  || Request::is('polygon/keys') || Request::is('eth/keys') || Request::is('litecoin/keys') || Request::is('bnb/keys') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-key"></i> <span> Crypto PrivateKeys</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
                <li class="{{ Request::is('btc/keys') ? 'active' : '' }}"> <a href="{{route('cms.btc.keys')}}"><i class="fa fa-circle-o"></i> BTC</a> </li>
                <li class="{{ Request::is('eth/keys') ? 'active' : '' }}"><a href="{{route('cms.eth.keys')}}"><i class="fa fa-circle-o"></i> ETH </a></li>
                <li class="{{ Request::is('litecoin/keys') ? 'active' : '' }}"><a href="{{route('cms.ltc.keys')}}"><i class="fa fa-circle-o"></i> LITECOIN</a></li>
                <li class="{{ Request::is('polygon/keys') ? 'active' : '' }}"><a href="{{route('cms.pol.keys')}}"><i class="fa fa-circle-o"></i> POLYGON </a></li>
                <li class="{{ Request::is('dogecoin/keys') ? 'active' : '' }}"><a href="{{route('cms.dog.keys')}}"><i class="fa fa-circle-o"></i> DOGECOIN </a></li>
            </ul>
          </li>
          <li class="treeview {{ Request::is('giftcards')||Request::is('giftcard/customer')|| Request::is('giftcard/activities') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-lock"></i> <span> GiftCard Module</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ Request::is('giftcard/customer') ? 'active' : '' }}"> <a href="{{route('cms.giftcard.customer')}}"><i class="fa fa-circle-o"></i> Customer</a> </li>
              <li class="{{ Request::is('giftcards') ? 'active' : '' }}"><a href="{{route('cms.giftcards')}}"><i class="fa fa-circle-o"></i> Gift Cards </a></li>
              <li class="{{ Request::is('giftcard/activities') ? 'active' : '' }}"><a href="{{route('cms.giftcard.activities')}}"><i class="fa fa-circle-o"></i> Gift Cards Activities </a></li>
            </ul>
          </li>
          <li class="treeview {{ Request::is('virtual-card-requests')|| Request::is('virtual-cards')|| Request::is('virtual-accounts') ? 'active open' : '' }}">
            <a href="#">
              <i class="fa fa-cc-mastercard"></i> <span> Virtual Accounts </span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="{{ Request::is('virtual-card-requests') ? 'active' : '' }}"> <a href="{{route('cms.virtual.requests')}}"><i class="fa fa-circle-o"></i> Virtual Card Requests</a> </li>
              <li class="{{ Request::is('virtual-accounts') ? 'active' : '' }}"><a href="{{route('cms.virtual.accounts')}}"><i class="fa fa-circle-o"></i> Virtual Accounts </a></li>
              <li class="{{ Request::is('virtual-cards') ? 'active' : '' }}"><a href="{{route('cms.virtual.cards')}}"><i class="fa fa-circle-o"></i> Virtual Cards </a></li>
            </ul>
          </li>
        <li class="treeview {{ Request::is('all/users') || Request::is('wallet/manager') || Request::is('users/accountnumbers')|| Request::is('users/registered/today') || Request::is('users/registered/today')|| Request::is('add/user') ? 'active open' : '' }}">
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
            <li class="{{ Request::is('all/users') ? 'active' : '' }}"><a href="{{route('cms.wallet')}}"><i class="fa fa-circle-o"></i> Update User Wallet</a></li>
            <li class="{{ Request::is('users/accountnumbers') ? 'active' : '' }}"><a href="{{route('cms.accountnumber')}}"><i class="fa fa-circle-o"></i> Account Numbers</a></li>
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
            <i class="fa fa-cogs"></i> <span> Site Settings</span>
          </a>
        </li>
        <li class="{{ Request::is('user-activities') ? 'active' : '' }}">
            <a href="{{route('cms.user.activities')}}">
              <i class="fa fa-bar-chart"></i> <span> User Activities</span>
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
