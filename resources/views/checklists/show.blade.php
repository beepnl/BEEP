@extends('layouts.app')

@section('page-title') {{ __('beep.checklist').': '.(isset($checklist->name) ? $checklist->name : __('crud.Item')) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($checklist->name) ? $checklist->name : __('crud.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('checklists.edit', $checklist->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $checklist->id }}</td>
                    </tr>
                    <tr><th> Name </th><td> {{ $checklist->name }} </td></tr>
                    <tr><th> Type </th><td> {{ $checklist->type }} </td></tr>
                    <tr><th> Description </th><td> {{ $checklist->description }} </td></tr>
                    <tr><th> Categories </th>
                        <td style="border-left: 1px solid #999;">
                            <div>
                                @include('categories.partials.tree-export', ['categories'=>$items])
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
