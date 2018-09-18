@extends('layouts.app')

@section('page-title') {{ __('beep.InspectionItem').': '.(isset($inspectionitem->name) ? $inspectionitem->name : __('crud.Item')).' ('.$inspectionitem->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($inspectionitem->name) ? $inspectionitem->name : __('crud.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('inspection-items.edit', $inspectionitem->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $inspectionitem->id }}</td>
                    </tr>
                    <tr><th> Value </th><td> {{ $inspectionitem->value }} </td></tr><tr><th> Inspection Id </th><td> {{ $inspectionitem->inspection_id }} </td></tr><tr><th> Category Id </th><td> {{ $inspectionitem->category_id }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
