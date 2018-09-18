@component('mail::message')
#{{ __('export.export_data') }}

{{ __('export.export_text') }}

{{__('export.enjoy')}}<br>
{{ config('app.name') }}
@endcomponent
