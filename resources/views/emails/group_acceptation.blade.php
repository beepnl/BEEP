@component('mail::message')
{{ __('group.Dear') }} {{$name}},

{{ $user }}
{{ __('group.accepted') }}
{{ $group }}

{{ config('app.name') }}
@endcomponent
