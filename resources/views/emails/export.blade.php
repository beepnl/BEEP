@component('mail::message')
{{ __('alert.Dear') }} {{$name}},

{{ __('export.export_text') }}

{{__('export.enjoy')}}<br>
{{ config('app.name') }}
@endcomponent
