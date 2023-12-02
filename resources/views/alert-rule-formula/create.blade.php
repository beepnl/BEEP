@extends('layouts.app')

@section('page-title') {{ __('crud.create', ['item'=>__('beep.AlertRuleFormula')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.create', ['item'=>__('beep.AlertRuleFormula')]) }}
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

            <form method="POST" action="{{ route('alert-rule-formula.store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}

                @include ('alert-rule-formula.form')

            </form>


        @endslot
    @endcomponent
@endsection
