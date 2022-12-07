@component('mail::message')
{{ __('samplecode.Dear') }} {{$name}},

@component('mail::panel')

{{ __('samplecode.result_text', ['code'=>$code]) }}

{{ __('samplecode.Hive', ['hive'=>$hive]) }}

@component('mail::button', ['url' => $link])
{{ __('samplecode.view_results') }}
@endcomponent

@endcomponent

@endcomponent
