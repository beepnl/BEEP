@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' '.__('beep.DashboardGroup').': '.(isset($dashboardgroup->name) ? $dashboardgroup->name : '') }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.edit').' '.__('crud.attributes') }}
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

            <form method="POST" action="{{ route('dashboard-group.update',$dashboardgroup->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                
                {{ method_field('PATCH') }}
                {{ csrf_field() }}

                @include ('dashboard-group.form', ['submitButtonText' => 'Update'])

            </form>


      @endslot
    @endcomponent
@endsection
