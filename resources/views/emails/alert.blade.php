@component('mail::message')
{{ __('alert.Dear') }} {{$name}},

{{ __('alert.alert_text', ['date' => $display_date_local, 'alertrule'=>$alert->alert_rule_name]) }}

@component('mail::panel')
@component('mail::table')
| {{ __('alert.Location') }} | {{ __('alert.Name') }} |
| :-- | :-- |
| {{ isset($alert->device_name) ? __('general.Device').' | '.$alert->device_name : ' | ' }} |
| {{ isset($alert->location_name) ? __('beep.Location').' | '.$alert->location_name : ' | ' }} |
| {{ isset($alert->hive_name) ? __('beep.Hive').' | '.$alert->hive_name : ' | ' }} |
@endcomponent

{{ isset($alert->alert_function) ? ucfirst($alert->alert_function) : '' }}

@if(isset($last_values_string))
{{ __('alert.alert_value') }}
{{ $last_values_string }}
@endif
@component('mail::button', ['url' => $url])
{{ __('alert.View_alerts') }}
@endcomponent

@component('mail::button', ['url' => $url_settings, 'color' => 'default'])
{{ __('alert.disable_text') }}
@endcomponent
@endcomponent

@endcomponent
