@extends('layouts.app')

@section('page-title') {{ __('crud.show', ['item'=>__('beep.categoryinput')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.show', ['item'=>__('beep.categoryinput')]) }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('categoryinputs.edit', $categoryinput->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <div class="row">
                <div class="col-md-12">

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>ID</th><td>{{ $categoryinput->id }}</td>
                                    </tr>
                                    <tr><th> Name </th><td> {{ $categoryinput->name }} </td></tr><tr><th> Type </th><td> {{ $categoryinput->type }} </td></tr><tr><th> Min </th><td> {{ $categoryinput->min }} </td></tr><tr><th> Max </th><td> {{ $categoryinput->max }} </td></tr><tr><th> Decimals </th><td> {{ $categoryinput->decimals }} </td></tr><tr><th> Icon </th><td> {{ $categoryinput->icon }} </td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        @endslot
    @endcomponent
@endsection
