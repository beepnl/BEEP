@extends('layouts.app')

@section('page-title') {{ __('beep.InspectionSvg').': '.(isset($inspectionsvg->name) ? $inspectionsvg->name : __('general.Item')).' ('.$inspectionsvg->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($inspectionsvg->name) ? $inspectionsvg->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('inspection-svg.edit', $inspectionsvg->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $inspectionsvg->id }}</td>
                    </tr>
                    <tr><th> User Id </th><td> {{ $inspectionsvg->user_id }} </td></tr><tr><th> Checklist Id </th><td> {{ $inspectionsvg->checklist_id }} </td></tr><tr><th> Name </th><td> {{ $inspectionsvg->name }} </td></tr><tr><th> Svg </th><td> {{ $inspectionsvg->svg }} </td></tr><tr><th> Pages </th><td> {{ $inspectionsvg->pages }} </td></tr><tr><th> Last Print </th><td> {{ $inspectionsvg->last_print }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
