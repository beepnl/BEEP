<li class="dropdown tasks-menu" >
  <!-- Menu Toggle Button -->
  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
    <img style="width:20px;" src="/img/flags/{{LaravelLocalization::getCurrentLocale()}}.svg"/>
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu" role="menu">
    <li class="header">{{ __('general.switch_language') }}</li>
    <li>
      <!-- Inner menu: contains the languages -->
      <ul class="menu">
        
        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
        <li>
            <a rel="alternate" hreflang="{{$localeCode}}" href="{{LaravelLocalization::getLocalizedURL($localeCode) }}">
                <img style="width:30px;" src="/img/flags/{{$localeCode}}.svg"/>
                <span>{{ $properties['native'] }}</span>
            </a>
        </li>
        @endforeach

      </ul>
    </li>
  </ul>

</li>