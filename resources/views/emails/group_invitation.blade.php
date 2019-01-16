@component('mail::message')
{{ __('group.group_data') }}

{{ __('group.group_text') }}

{{ $admin ? __('group.Admin').' '.__('group.of').' '.__('group.group') : __('group.Group') }}:
{{ $group->name }}

{{ isset($group->description) ? '('.$group->description.')' : '' }}

@component('mail::button', ['url' => $acceptUrl])
{{ __('group.Accept_invite') }}
@endcomponent


{{__('group.enjoy')}}<br>
{{ config('app.name') }}
@endcomponent
