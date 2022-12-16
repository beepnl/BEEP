@component('mail::message')
{{ __('samplecode.Dear') }} {{$name}},

@component('mail::panel')

{{ __('samplecode.result_text', ['code'=>$code]) }}

{{ __('samplecode.Hive', ['hive'=>$hive]) }}

@component('mail::button', ['url' => $link])
{{ __('samplecode.view_results') }}
@endcomponent

@component('mail::button', ['url' => 'https://beepsupport.freshdesk.com/en/support/solutions/articles/60000815800-sample-data-clarification', 'color'=>'default'])
{{ __('samplecode.data_clarification') }}
@endcomponent

@endcomponent

@endcomponent
