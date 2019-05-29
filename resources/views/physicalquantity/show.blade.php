@extends('layouts.app')

@section('page-title') {{ __('beep.physicalquantity').': '.(isset($physicalquantity->name) ? $physicalquantity->name : __('general.Item')).' ('.$physicalquantity->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($physicalquantity->name) ? $physicalquantity->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('physicalquantity.edit', $physicalquantity->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $physicalquantity->id }}</td>
                    </tr>
                    <tr><th> Name </th><td> {{ $physicalquantity->name }} </td></tr>
                    <tr><th> Unit </th><td> {{ $physicalquantity->unit }} </td></tr>
                    <tr><th> Abbreviation </th><td> {{ $physicalquantity->abbreviation }} </td></tr>
                    <tr><th> Low value </th><td> {{ $measurement->low_value }} </td></tr>
                    <tr><th> High value </th><td> {{ $measurement->high_value }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
