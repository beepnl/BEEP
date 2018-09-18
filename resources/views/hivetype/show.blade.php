@extends('layouts.app')

@section('page-title') {{ __('crud.show', ['item'=>__('beep.hivetype')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.show', ['item'=>__('beep.hivetype')]) }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('hivetype.edit', $hivetype->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <div class="row">
                <div class="col-md-12">

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>ID</th><td>{{ $hivetype->id }}</td>
                                    </tr>
                                    <tr><th> Name </th><td> {{ $hivetype->name }} </td></tr><tr><th> Type </th><td> {{ $hivetype->type }} </td></tr><tr><th> Image </th><td> {{ $hivetype->image }} </td></tr><tr><th> Continents </th><td> {{ $hivetype->continents }} </td></tr><tr><th> Info Url </th><td> {{ $hivetype->info_url }} </td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        @endslot
    @endcomponent
@endsection
