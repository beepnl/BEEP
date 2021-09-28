@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{asset('https://app.beep.nl/img/beep-icon-logo-small.png')}}" alt="{{ config('app.name') }}"/>
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
© {{ date('Y') }} team {{ config('app.name') }}.
@endcomponent
@endslot
@endcomponent
