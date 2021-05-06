@extends('layouts.app')

@section('page-title') {{ __('beep.Alert').': '.(isset($alert->name) ? $alert->name : __('general.Item')).' ('.$alert->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($alert->name) ? $alert->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('alert.edit', $alert->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $alert->id }}</td>
                    </tr>
                    <tr>
                        <th>Created at</th>
                        <td>{{ $alert->created_at }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>{{ isset($alert->user_id) ? $alert->user->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th> Alert Rule Id </th>
                        <td> {{ $alert->alert_rule_id }} </td>
                    </tr>
                    <tr>
                        <th> Alert Function </th>
                        <td> {{ $alert->alert_function }} </td>
                    </tr>
                    <tr>
                        <th> Alert Value </th>
                        <td> {{ $alert->alert_value }} </td>
                    </tr>
                    <tr>
                        <th> Measurement Id </th>
                        <td> {{ $alert->measurement_id }} </td>
                    </tr>
                    <tr>
                        <th> Show </th>
                        <td> {{ $alert->show }} </td>
                    </tr>
                    <tr>
                        <th> Location Name </th>
                        <td> {{ $alert->location_name }} </td>
                    </tr>
                    <tr>
                        <th> Hive Name </th>
                        <td> {{ $alert->hive_name }} </td>
                    </tr>
                    <tr>
                        <th> Device Name </th>
                        <td> {{ $alert->device_name }} </td>
                    </tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
