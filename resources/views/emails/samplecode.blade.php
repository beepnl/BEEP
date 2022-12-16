@component('mail::message')
{{ __('samplecode.Dear') }} {{$name}},

@component('mail::panel')

{{ __('samplecode.result_text', ['code'=>$code]) }}

{{ __('samplecode.Hive', ['hive'=>$hive]) }}

@component('mail::button', ['url' => $link])
{{ __('samplecode.view_results') }}
@endcomponent

@component('mail::button', ['url' => __('samplecode.lab_data_url'), 'color'=>'default'])
{{ __('samplecode.lab_data_expl') }}
@endcomponent

@endcomponent

@endcomponent
