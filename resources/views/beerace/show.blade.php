@extends('layouts.app')

@section('page-title') {{ __('crud.show', ['item'=>__('beep.beerace')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.show', ['item'=>__('beep.beerace')]) }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('beerace.edit', $beerace->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <div class="row">
                <div class="col-md-12">

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>ID</th><td>{{ $beerace->id }}</td>
                                    </tr>
                                    <tr><th> Name </th><td> {{ $beerace->name }} </td></tr><tr><th> Type </th><td> {{ $beerace->type }} </td></tr><tr><th> Synonyms </th><td> {{ $beerace->synonyms }} </td></tr><tr><th> Continents </th><td> {{ $beerace->continents }} </td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        @endslot
    @endcomponent
@endsection
