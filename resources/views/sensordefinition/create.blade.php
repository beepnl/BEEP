@extends('layouts.app')

@section('page-title') {{ __('crud.create', ['item'=>__('beep.SensorDefinition')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.create', ['item'=>__('beep.SensorDefinition')]) }}
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

            @if ($errors->any())
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('sensordefinition.store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}

                @include ('sensordefinition.form', ['sensordefinition'=>new App\SensorDefinition(), 'submitButtonText'=>'Create'])

            </form>


        @endslot
    @endcomponent
@endsection
