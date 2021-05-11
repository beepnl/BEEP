@component('mail::message')
{{ __('alert.Dear') }} {{$name}},

{{ __('alert.alert_text', ['date' => $alert->created_at, 'alertrule'=>$alert->alert_rule_name]) }}

{{ isset($alert->device_name) ? __('general.Device').': '.$alert->device_name : '' }}
{{ isset($alert->location_name) ? __('beep.Location').': '.$alert->location_name : '' }}
{{ isset($alert->hive_name) ? __('beep.Hive').': '.$alert->hive_name : '' }}

{{ isset($alert->alert_function) ? $alert->alert_function : '' }}

{{ __('alert.alert_value', ['value' => $alert->alert_value]) }}

@component('mail::button', ['url' => $alertsUrl])
{{ __('alert.View_alerts') }}
@endcomponent


{{__('alert.disable_text')}}<br>
{{ config('app.name') }}
@endcomponent
