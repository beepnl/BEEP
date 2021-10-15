<li class="dropdown user user-menu" >
  <!-- Menu Toggle Button -->
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <!-- The user image in the navbar-->
    <img src="{{ Auth::user()->avatar }}" class="user-image" alt="User Image">
    <!-- hidden-xs hides the username on small devices so only the image appears. -->
    <span class="hidden-xs">{{ Auth::user()->name }}</span>
  </a>
  <ul class="dropdown-menu">
    <!-- The user image in the menu -->
    <li class="user-header">
      <img src="{{ Auth::user()->avatar }}" class="img-circle" alt="User Image">
      <p>
        {{ Auth::user()->name }}
        <small>{{ __('general.member_since') }}: {{ Auth::user()->created_at }}</small>
      </p>
    </li>
    <!-- Menu Body -->
    <!--li class="user-body">
      <div class="row">
        <div class="col-xs-4 text-center">
          <a href="#">Followers</a>
        </div>
        <div class="col-xs-4 text-center">
          <a href="#">Sales</a>
        </div>
        <div class="col-xs-4 text-center">
          <a href="#">Friends</a>
        </div>
      </div>
    </li-->
    <!-- Menu Footer-->
    <li class="user-footer">
      <div class="pull-left">
        <a href="{{ route('users.edit', Auth::user()->id) }}" class="btn btn-default btn-flat">{{ __('general.Profile') }}</a>
      </div>
      <div class="pull-right">
          <a href="{{ route('logout') }}" class="btn btn-default btn-flat"
              onclick="event.preventDefault();
                       document.getElementById('logout-form').submit();">
              {{ __('general.Logout') }}
          </a>

          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
              {{ csrf_field() }}
          </form>
      </div>
    </li>
  </ul>
</li>
