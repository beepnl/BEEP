@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.webapp_url')])
<img style="width:200px;" src="https://assets.beep.nl/static/email/beep-icon-logo.png" alt="{{ config('app.name') }}"/>
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@if (isset($subcopy))
@slot('subcopy')
@component('mail::subcopy')
    {{ $subcopy }}
@endcomponent
@endslot
@endif

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
{{ date('Y') }} team {{ config('app.name') }}
@endcomponent
@endslot
@endcomponent
