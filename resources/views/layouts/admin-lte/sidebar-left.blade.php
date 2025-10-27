<aside class="main-sidebar">

  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

    <!-- Sidebar user panel (optional) -->
    <div class="user-panel">
      <div class="pull-left image">
        <img src="{{ Auth::user()->avatar }}" class="img-circle" alt="User Image">
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
      @role(['superadmin','admin'])
        <li class="header">{{ __('general.admin').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('dashboard.index') ? 'active' : '' }}"><a href="{{ route('dashboard.index') }}"><i class="fa fa-dashboard"></i><span>{{ __('general.dashboard') }}</span></a></li>
        
        
        <li class="treeview {{ preg_match('(categories|categoryinputs|physicalquantity|taxonomy)', Request::url()) === 1 ? 'active' : '' }}">
          <a href="#">
            <span><i class="fa fa-list"></i></span>
            <span>{{ __('general.Taxonomy') }}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ Route::currentRouteNamed('categories.index') ? 'active' : '' }}"><a href="{{ route('categories.index') }}"><i class="fa fa-list"></i><span>{{ __('general.Taxonomy') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('categoryinputs.index') ? 'active' : '' }}"><a href="{{ route('categoryinputs.index') }}"><i class="fa fa-list-ul"></i><span>{{ __('beep.CategoryInputs') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('physicalquantity.index') ? 'active' : '' }}"><a href="{{ route('physicalquantity.index') }}"><i class="fa fa-balance-scale"></i><span>{{ __('beep.PhysicalQuantity') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('taxonomy.display') ? 'active' : '' }}"><a href="{{ route('taxonomy.display') }}"><i class="fa fa-circle-o"></i><span>{{ __('general.Taxonomy') }} {{ __('beep.visual') }}</span></a></li>
          </ul>
        </li>
        
        <li class="treeview {{ preg_match('(devices|flash-log|measurement|sensordefinition)', Request::url()) === 1 ? 'active' : '' }}">
          <a href="#">
            <span><i class="fa fa-wifi"></i></span>
            <span>{{ __('general.Devices') }}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ Route::currentRouteNamed('devices.index') ? 'active' : '' }}"><a href="{{ route('devices.index') }}"><i class="fa fa-wifi"></i><span>{{ __('general.Devices') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('flash-log.index') ? 'active' : '' }}"><a href="{{ route('flash-log.index') }}"><i class="fa fa-line-chart"></i><span>Flash log</span></a></li>
            <li class="{{ Route::currentRouteNamed('devices.data') ? 'active' : '' }}"><a href="{{ route('devices.data') }}"><i class="fa fa-clock-o"></i><span>Device data</span></a></li>
            <li class="{{ Route::currentRouteNamed('measurement.index') ? 'active' : '' }}"><a href="{{ route('measurement.index') }}"><i class="fa fa-circle"></i><span>{{ __('general.Measurements') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('sensordefinition.index') ? 'active' : '' }}"><a href="{{ route('sensordefinition.index') }}"><i class="fa fa-cog"></i><span>{{ __('general.SensorDefinitions') }}</span></a></li>
          </ul>
        </li>
        
        <!-- <li class="{{ Route::currentRouteNamed('research.index') ? 'active' : '' }}"><a href="{{ route('research.index') }}"><i class="fa fa-graduation-cap"></i><span>{{ __('beep.Researches') }}</span></a></li> -->
      @endrole

      @if(Auth::user()->researchMenuOption())
      <li class="header">{{ 'Research data' }}</li>
      <li class="{{ Route::currentRouteNamed('research.index') ? 'active' : '' }}"><a href="{{ route('research.index') }}"><i class="fa fa-graduation-cap"></i><span>{{ __('beep.Researches') }}</span></a></li>
      @endrole
      
      @role(['superadmin','admin','translator'])
        <li class="header">{{ __('general.Translations').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('languages.index') ? 'active' : '' }}"><a href="{{ route('languages.index') }}"><i class="fa fa-flag"></i><span>{{ __('general.Languages') }}</span></a></li>
        <li class="{{ Route::currentRouteNamed('translations.index') ? 'active' : '' }}"><a href="{{ route('translations.index') }}"><i class="fa fa-font"></i><span>{{ __('general.Translations') }}</span></a></li>
      @endrole

      @role('manager')
        <li class="header">{{ __('general.manager').' '.__('general.menu') }}</li>
        <li class="{{ Route::currentRouteNamed('devices.index') ? 'active' : '' }}"><a href="{{ route('devices.index') }}"><i class="fa fa-cube "></i><span>{{ __('general.Devices') }}</span></a></li>
        
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole

      @role(['superadmin','admin','lab'])
        <li class="header">LAB results</li>
        <li class="{{ Route::currentRouteNamed('sample-code.upload') ? 'active' : '' }}"><a href="{{ route('sample-code.upload') }}"><i class="fa fa-qrcode "></i><span>Upload results</span></a></li>
        
        {{-- <li class="{{ Route::currentRouteNamed('groups.index') ? 'active' : '' }}"><a href="{{ route('groups.index') }}"><i class="fa fa-cubes"></i><span>{{ __('general.Groups') }}</span></a></li> --}}
      @endrole


      <li class="header">{{ __('general.User').' '.__('general.menu') }}</li>
      <li class="{{ Route::currentRouteNamed('checklists.index') ? 'active' : '' }}"><a href="{{ route('checklists.index') }}"><i class="fa fa-list"></i><span>{{ __('beep.Checklists') }}</span></a></li>
      <li class="{{ Route::currentRouteNamed('inspections.index') ? 'active' : '' }}"><a href="{{ route('inspections.index') }}"><i class="fa fa-check-circle"></i><span>{{ __('beep.Inspections') }}</span></a></li>

      <li class="header">{{ __('beep.Webapp') }}</li>
      <li><a href="/webapp" target="_blank"><i class="fa fa-mobile-phone"></i><span>{{ __('beep.Webapp') }}</span></a></li>

      @role('superadmin')
      <li class="header">{{ __('general.superadmin').' '.__('general.menu') }} <i class="fa fa-warning" title="NB: ONLY USE IF YOU ARE ABSOLUTELY SURE WHAT YOU ARE DOING!"></i></li>
      <li class="treeview {{ preg_match('(users|roles|permissions|image|alert-rule|alert-rule-formula|calculation-model|alert|sample-code|hive-tags|checklist-svgs)', Request::url()) === 1 ? 'active' : '' }}">
          <a href="#">
            <span><i class="fa fa-list"></i></span>
            <span>{{ __('general.superadmin').' '.__('general.menu') }}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ Route::currentRouteNamed('users.index') ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fa fa-user-circle-o"></i><span>{{ __('general.Users') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('roles.index') ? 'active' : '' }}"><a href="{{ route('roles.index') }}"><i class="fa fa-address-book-o"></i><span>{{ __('general.Roles') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('permissions.index') ? 'active' : '' }}"><a href="{{ route('permissions.index') }}"><i class="fa fa-lock"></i><span>{{ __('general.Permissions') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('image.index') ? 'active' : '' }}"><a href="{{ route('image.index') }}"><i class="fa fa-photo"></i><span>{{ __('general.Images') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('alert-rule.index') ? 'active' : '' }}"><a href="{{ route('alert-rule.index') }}"><i class="fa fa-exclamation-circle"></i><span>{{ __('general.AlertRules') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('alert-rule-formula.index') ? 'active' : '' }}"><a href="{{ route('alert-rule-formula.index') }}"><i class="fa fa-superscript"></i><span>{{ __('general.AlertRuleFormulas') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('calculation-model.index') ? 'active' : '' }}"><a href="{{ route('calculation-model.index') }}"><i class="fa fa-calculator"></i><span>Calculation models</span></a></li>
            <li class="{{ Route::currentRouteNamed('alert.index') ? 'active' : '' }}"><a href="{{ route('alert.index') }}"><i class="fa fa-bell"></i><span>{{ __('general.Alerts') }}</span></a></li>
            <li class="{{ Route::currentRouteNamed('sample-code.index') ? 'active' : '' }}"><a href="{{ route('sample-code.index') }}"><i class="fa fa-qrcode"></i><span>Sample codes</span></a></li>
            <li class="{{ Route::currentRouteNamed('hive-tags.index') ? 'active' : '' }}"><a href="{{ route('hive-tags.index') }}"><i class="fa fa-qrcode"></i><span>Hive tags</span></a></li>
            <li class="{{ Route::currentRouteNamed('checklist-svg.index') ? 'active' : '' }}"><a href="{{ route('checklist-svg.index') }}"><i class="fa fa-qrcode"></i><span>Inspection SVG</span></a></li>
          </ul>
        </li>
        @endrole
    </ul>
    <!-- /.sidebar-menu -->

  </section>
  <!-- /.sidebar -->
</aside>