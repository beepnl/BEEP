@component('mail::message')
{{ __('group.Dear') }} {{$name}},

{{ $user }}
{{ __('group.refused') }}
{{ $group }}

{{ config('app.name') }}
@endcomponent
