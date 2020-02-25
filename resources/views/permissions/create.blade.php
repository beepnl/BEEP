@extends('layouts.app')

@section('page-title') {{ __('crud.create', ['item'=>__('general.permission')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.create', ['item'=>__('general.permission')]) }}
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

            <form method="POST" action="{{ route('permissions.store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}

                @include ('permissions.form', ['permission'=>new App\Permission, 'submitButtonText'=>'Create'])

            </form>


        @endslot
    @endcomponent
@endsection
