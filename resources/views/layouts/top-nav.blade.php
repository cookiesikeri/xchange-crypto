<header class="main-header">
    <!-- Logo -->
    <a href="index.html" class="logo">
      <!-- mini logo-->
      <span class="logo-mini"><b>C</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Cross</b>Admin</span>
    </a>
    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="{{asset('img/admins.png')}}" class="user-image" alt="User Image">
            </a>
            <ul class="dropdown-menu scale-up">
              <!-- User image -->
              <li class="user-header">
                <img src="{{asset('img/admins.png')}}" class="img-responsive" alt="User Image">

                <p>
                    {{ucfirst(Auth::User()->name)}}
                    <small>{{ucfirst(Auth::User()->email)}}</small>
                </p>
              </li>

              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                    <a href="{{route('cms.admin.profile')}}" class="btn btn-default btn-flat">Profile</a>
                  </div>
                  <div class="pull-right">
                    <a href="{{route('logout')}}" class="btn btn-default btn-flat">Sign out</a>
                  </div>
              </li>
            </ul>
          </li>
          <!-- Messages-->
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-envelope"></i>
              <span class="label label-danger">{{ \App\Models\ContactMessage::where('is_treated', 0)->count() }}</span>
            </a>
            <ul class="dropdown-menu scale-up">
              <li class="header">You have {{ \App\Models\ContactMessage::where('is_treated', 0)->count() }} messages</li>

              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu inner-content-div">
                    @if(count($unreadm) < 1)

                    <li>
                        <p>No record currently available</p>
                    </li>

                    @else
                    @foreach($unreadm as $key=>$state)
                  <li><!-- start message -->
                    <a href="#">
                      <div class="pull-left">
                        <img src="{{asset('img/admins.png')}}" class="img-circle" alt="User Image">
                      </div>
                      <div class="mail-contnet">
                         <h4>
                            {{ucfirst($state['name'])}}
                            <small><i class="fa fa-clock-o"></i> {{ date('M j, Y h:ia', strtotime($state['created_at'])) }}</small>
                         </h4>
                         <span>{!! Str::limit($state->subject ,50) !!}...</span>
                      </div>
                    </a>
                  </li>
                  <!-- end message -->
                  @endforeach
                  @endif

                </ul>
              </li>
              <li class="footer"><a href="{{route('cms.messages')}}">See all messages</a></li>
            </ul>
          </li>

          <!-- Control Sidebar Toggle Button -->
          <li>
            <a href="{{route('cms.settings')}}"><i class="fa fa-cog fa-spin"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>
