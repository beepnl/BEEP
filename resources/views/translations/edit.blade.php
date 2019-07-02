@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('general.translations')]) }}
@endsection

@section('content')

    @component('components/box')
        @slot('title')
            <img src="/img/{{ $language->icon }}" style="width: 30px;"> App {{ __('general.translations') }} {{ $language->name_english }} 
        @endslot

        @slot('body')

            @if (isset($language) && isset($categories))

                <div class="col-xs-12">
                    <h4>How to translate app texts?</h4>
                    <ol>
                        <li>Download the <a href="/js/languages/{{ $language->twochar }}.js">{{ $language->name_english }} app translations Javascript file ({{ $language->twochar }}.js) here</a></li>
                        <li>Open it in a text editor</li>
                        <li>ONLY change all the English texts after the colons (:) between 'single quotes' (and leave the quotes there). You can use all UTF-8 characters.</li>
                        <li>If translated, please send the file to <a href="mailto:pim@beep.nl">pim@beep.nl</a> to create a new app language and include it in the app files.</li> 
                    </ol>
                    <p><strong>The translations will be made available in the on-line Beep app as soon as both the app texts and the taxonomy fields (see box below) are filled for a new language.</strong></p>
                </div>

            @endif

        @endslot
    @endcomponent

    {!! Form::open([ 'route' => ['translations.update', $language->id], 'method' => 'PATCH' ]) !!}

    @component('components/box')
        @slot('title')
            <img src="/img/{{ $language->icon }}" style="width: 30px;"> {{ __('beep.Checklist') }} {{ __('general.translations') }} {{ $language->name_english }}
        @endslot

        @slot('action')
        	{!! Form::submit('Update translations', [ 'class' => 'btn btn-primary btn-block small' ]) !!}
        @endslot

        @slot('body')

            <div class="col-xs-12">
                <h4>How to translate checklist texts?</h4>
                <p>Just enter the correct <span class="text-success"><strong>{{ $language->name_english }} translations</strong></span> in the input fields of the third colomn below.<br>
                The translations below are for the hive checklist / taxonomy that is available in the app through editing your hive checklist. It contains all the available options in the taxonomy.</p>
            </div>


			@if (isset($language) && isset($categories))

                @include('translations.partials.input', ['categories'=>$categories, 'language'=>$language])

            @endif

            <div class="form-group">
                {!! Form::submit('Update translations', [ 'class' => 'btn btn-primary btn-block' ]) !!}
            </div>


        @endslot
    @endcomponent

    {!! Form::close() !!}
    
@endsection