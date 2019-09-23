@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('general.translations')]) }}
@endsection

@section('content')

    @component('components/box')
        @slot('title')
            {{ __('crud.select', ['item'=>__('general.language')]) }}
        @endslot

        @slot('action')

        @endslot

        @slot('body')
               
            @foreach(App\Language::all()->sortBy('name_english') as $lang )

                <div style="margin: 20px;"><a href="{{ route('translations.index') }}/{{$lang->id}}"><img src="/img/{{ $lang->icon }}" style="width: 40px; display: inline-block; margin-right: 20px;"> {{ $lang->name_english }} ({{ $lang->name }})</a></div>

            @endforeach

        @endslot
    @endcomponent

@endsection