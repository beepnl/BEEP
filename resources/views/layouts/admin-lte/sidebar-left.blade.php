<aside class="main-sidebar">

  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

    <!-- Sidebar user panel (optional) -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="/uploads/avatars/{{ Auth::user()->avatar }}" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p>{{ Auth::user()->name }}</p>
        <!-- Status -->
        @role('superadmin')<p><i class="fa fa-user-secret"></i> {{ __('general.superadmin') }}</p>   @endrole
        @role('admin')     <p><i class="fa fa-user-md"></i> {{ __('general.admin') }}</p>            @endrole
        @role('manager')   <p><i class="fa fa-user"></i> {{ __('general.manager') }}</p>             @endrole
      </div>
    </div>

    <!-- search form (Optional) -->
    <!--form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search...">
            <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
      </div>
    </form-->
    <!-- /.search form -->

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">
      <!-- Optionally, you can add icons to the links -->
        @role('superadmin')
        <li class="header">{{ __('general.superadmin').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('roles.index') ? 'active' : '' }}"><a href="{{ route('roles.index') }}"><i class="fa fa-address-book-o"></i><span>{{ __('general.Roles') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('users.index') ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fa fa-user-circle-o"></i><span>{{ __('general.Users') }}</span></a></li>
        {{-- <li class="{{ Route::currentRouteNamed('sensors.index') ? 'active' : '' }}"><a href="{{ route('sensors.index') }}"><i class="fa fa-cube "></i><span>{{ __('general.Sensors') }}</span></a></li> --}}
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole
      @role('admin')
        <li class="header">{{ __('general.admin').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('users.index') ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fa fa-user-circle-o"></i><span>{{ __('general.Users') }}</span></a></li>
        {{-- <li class="{{ Route::currentRouteNamed('sensors.index') ? 'active' : '' }}"><a href="{{ route('sensors.index') }}"><i class="fa fa-cube "></i><span>{{ __('general.Sensors') }}</span></a></li> --}}
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole
      @role('manager')
        <li class="header">{{ __('general.manager').' '.__('general.menu') }}</li>
        {{-- <li class="{{ Route::currentRouteNamed('sensors.index') ? 'active' : '' }}"><a href="{{ route('sensors.index') }}"><i class="fa fa-cube "></i><span>{{ __('general.Sensors') }}</span></a></li> --}}
        
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole
      {{-- <li class="header">{{ __('general.menu_data') }}</li>
      <li>
          <a href="#"><i class="fa fa-dashboard"></i><span>{{ __('general.dashboard') }}</span></a>
      </li> 
      <li>
          <a href="#"><i class="fa fa-dot-circle-o"></i><span>{{ __('general.sensordata') }}</span></a>
      </li>
      <li>
          <a href="#"><i class="fa fa-bar-chart"></i><span>{{ __('general.dataanalysis') }}</span></a>
      </li>          --}}
    </ul>
    <!-- /.sidebar-menu -->

  </section>
  <!-- /.sidebar -->
</aside>