@extends('layouts.app')

@section('page-title') {{ __('crud.create', ['item'=>__('beep.checklist')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.create', ['item'=>__('beep.checklist')]) }}
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

            <form method="POST" action="{{ route('checklists.store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}

                @include ('checklists.form', ['users'=>$users, 'selectedUserId'=>$selectedUserId, 'submitButtonText' => 'Save'])

            </form>


        @endslot
    @endcomponent
@endsection
