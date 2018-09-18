@extends('layouts.app')

@section('page-title') {{ __('crud.show', ['item'=>__('beep.physicalquantity')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.show', ['item'=>__('beep.physicalquantity')]) }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('physicalquantity.edit', $physicalquantity->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <div class="row">
                <div class="col-md-12">

                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>ID</th><td>{{ $physicalquantity->id }}</td>
                                    </tr>
                                    <tr><th> Name </th><td> {{ $physicalquantity->name }} </td></tr><tr><th> Unit </th><td> {{ $physicalquantity->unit }} </td></tr><tr><th> Abbreviation </th><td> {{ $physicalquantity->abbreviation }} </td></tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        @endslot
    @endcomponent
@endsection
