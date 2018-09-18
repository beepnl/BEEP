@extends('layouts.app')

@section('page-title') {{ __('general.permission').': '.(isset($permission->name) ? $permission->name : __('general.Item')).' ('.$permission->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($permission->name) ? $permission->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('permissions.edit', $permission->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $permission->id }}</td>
                    </tr>
                    <tr><th> Name </th><td> {{ $permission->name }} </td></tr><tr><th> Display Name </th><td> {{ $permission->display_name }} </td></tr><tr><th> Description </th><td> {{ $permission->description }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
