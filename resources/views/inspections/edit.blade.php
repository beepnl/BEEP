@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' '.__('beep.Inspection').': '.(isset($inspection->name) ? $inspection->name : '') }}
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

            <label>{{__('beep.Inspection')}} items:</label><br>
            @if($inspection->items()->count() > 0)
            <table class="table table-responsive table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Value</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($inspection->items()->get() as $item)
                    <tr>
                        <td>{{$item->name()}}</td>
                        <td>{{$item->val()}}</td>
                        <td>{{$item->unit()}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @else
            -
            @endif

            <form method="POST" action="{{ route('inspections.update',$inspection->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                
                {{ method_field('PATCH') }}
                {{ csrf_field() }}

                @include ('inspections.form', ['submitButtonText' => 'Update'])

            </form>


      @endslot
    @endcomponent
@endsection
