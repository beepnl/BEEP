@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' '.__('beep.checklist').': '.(isset($checklist->name) ? $checklist->name : __('general.Item')) }}
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

            <form method="POST" action="{{ route('checklists.update',$checklist->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                
                {{ method_field('PATCH') }}
                {{ csrf_field() }}

                @include ('checklists.form', ['submitButtonText' => 'Update', 'selected'=>$selected, 'users'=>$users, 'selectedUserIds'=>$selectedUserIds, 'checklist'=>$checklist])

            </form>


      @endslot
    @endcomponent
@endsection
