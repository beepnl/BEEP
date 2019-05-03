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
        <li class="header">{{ __('general.Permissions').' '.__('general.menu') }} <i class="fa fa-warning" title="NB: ONLY USE IF YOU ARE ABSOLUTELY SURE WHAT YOU ARE DOING!"></i></li>
        <li class="{{ Route::currentRouteNamed('permissions.index') ? 'active' : '' }}"><a href="{{ route('permissions.index') }}"><i class="fa fa-lock"></i><span>{{ __('general.Permissions') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('roles.index') ? 'active' : '' }}"><a href="{{ route('roles.index') }}"><i class="fa fa-address-book-o"></i><span>{{ __('general.Roles') }}</span></a></li>
        
        <li class="header">{{ __('general.superadmin').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('dashboard.index') ? 'active' : '' }}"><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i><span>{{ __('general.dashboard') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('users.index') ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fa fa-user-circle-o"></i><span>{{ __('general.Users') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('sensors.index') ? 'active' : '' }}"><a href="{{ route('sensors.index') }}"><i class="fa fa-wifi"></i><span>{{ __('general.Sensors') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('measurement.index') ? 'active' : '' }}"><a href="{{ route('measurement.index') }}"><i class="fa fa-circle"></i><span>{{ __('general.Measurements') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('categories.index') ? 'active' : '' }}"><a href="{{ route('categories.index') }}"><i class="fa fa-list"></i><span>{{ __('general.Taxonomy') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('physicalquantity.index') ? 'active' : '' }}"><a href="{{ route('physicalquantity.index') }}"><i class="fa fa-balance-scale"></i><span>{{ __('beep.PhysicalQuantity') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('categoryinputs.index') ? 'active' : '' }}"><a href="{{ route('categoryinputs.index') }}"><i class="fa fa-list-ul"></i><span>{{ __('beep.CategoryInputs') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('taxonomy.display') ? 'active' : '' }}"><a href="{{ route('taxonomy.display') }}"><i class="fa fa-circle-o"></i><span>{{ __('general.Taxonomy') }} {{ __('beep.visual') }}</span></a></li>
        {{-- <li class="{{ Route::currentRouteNamed('beerace.index') ? 'active' : '' }}"><a href="{{ route('beerace.index') }}"><i class="fa fa-forumbee"></i><span>{{ __('beep.BeeRace') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('hivetype.index') ? 'active' : '' }}"><a href="{{ route('hivetype.index') }}"><i class="fa fa-archive"></i><span>{{ __('beep.HiveType') }}</span></a></li> --}}
        
        {{-- <li class="{{ Route::currentRouteNamed('sensors.index') ? 'active' : '' }}"><a href="{{ route('sensors.index') }}"><i class="fa fa-cube "></i><span>{{ __('general.Sensors') }}</span></a></li> --}}
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole

      @role('admin')
        <li class="header">{{ __('general.admin').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('users.index') ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fa fa-user-circle-o"></i><span>{{ __('general.Users') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('categories.index') ? 'active' : '' }}"><a href="{{ route('categories.index') }}"><i class="fa fa-list"></i><span>{{ __('general.Taxonomy') }}</span></a></li>
        {{-- <li class="{{ Route::currentRouteNamed('sensors.index') ? 'active' : '' }}"><a href="{{ route('sensors.index') }}"><i class="fa fa-cube "></i><span>{{ __('general.Sensors') }}</span></a></li> --}}
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole

      @role(['superadmin','admin','translator'])
        <li class="header">{{ __('general.Translations').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('languages.index') ? 'active' : '' }}"><a href="{{ route('languages.index') }}"><i class="fa fa-flag"></i><span>{{ __('general.Languages') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('translations.index') ? 'active' : '' }}"><a href="{{ route('translations.index') }}"><i class="fa fa-font"></i><span>{{ __('general.Translations') }}</span></a></li>
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
      <li class="header">{{ __('general.User').' '.__('general.menu') }}</li>
      <li class="{{ Route::currentRouteNamed('checklists.index') ? 'active' : '' }}"><a href="{{ route('checklists.index') }}"><i class="fa fa-list"></i><span>{{ __('beep.Checklists') }}</span></a></li>
      <li class="{{ Route::currentRouteNamed('inspections.index') ? 'active' : '' }}"><a href="{{ route('inspections.index') }}"><i class="fa fa-check-circle"></i><span>{{ __('beep.Inspections') }}</span></a></li>

      <li class="header">{{ __('beep.Webapp') }}</li>
      <li><a href="/webapp" target="_blank"><i class="fa fa-mobile-phone"></i><span>{{ __('beep.Webapp') }}</span></a></li>
    </ul>
    <!-- /.sidebar-menu -->

  </section>
  <!-- /.sidebar -->
</aside>