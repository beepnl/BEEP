@component('mail::message')
{{ __('group.Dear') }} {{$name}},

{{ __('group.group_text', ['invited_by' => $invited_by]) }}

{{ $admin ? __('group.Admin').' '.__('group.of').' '.__('group.group') : __('group.Group') }}:
{{ $group->name }}

{{ isset($group->description) ? '('.$group->description.')' : '' }}

@component('mail::button', ['url' => $acceptUrl])
{{ __('group.Accept_invite') }}
@endcomponent


{{__('group.enjoy')}}<br>
{{ config('app.name') }}
@endcomponent
