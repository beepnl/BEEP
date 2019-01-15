@component('mail::message')
{{ __('group.group_data') }}

{{ __('group.group_text') }}

{{ $group->name }}
{{ $admin ? 'Admin' : '' }}

{{__('group.enjoy')}}<br>
{{ config('app.name') }}
@endcomponent
