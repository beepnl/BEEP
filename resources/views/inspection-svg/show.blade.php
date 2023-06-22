@extends('layouts.app')

@section('page-title') {{ __('beep.ChecklistSvg').': '.(isset($checklistsvg->name) ? $checklistsvg->name : __('general.Item')).' ('.$checklistsvg->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($checklistsvg->name) ? $checklistsvg->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('checklist-svg.edit', $checklistsvg->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $checklistsvg->id }}</td>
                    </tr>
                    <tr><th> User Id </th><td> {{ $checklistsvg->user_id }} </td></tr><tr><th> Checklist Id </th><td> {{ $checklistsvg->checklist_id }} </td></tr><tr><th> Name </th><td> {{ $checklistsvg->name }} </td></tr><tr><th> Svg </th><td> {{ $checklistsvg->svg }} </td></tr><tr><th> Pages </th><td> {{ $checklistsvg->pages }} </td></tr><tr><th> Last Print </th><td> {{ $checklistsvg->last_print }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
