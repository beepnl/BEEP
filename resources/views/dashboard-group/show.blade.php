@extends('layouts.app')

@section('page-title') {{ __('beep.DashboardGroup').': '.(isset($dashboardgroup->name) ? $dashboardgroup->name : __('general.Item')).' ('.$dashboardgroup->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($dashboardgroup->name) ? $dashboardgroup->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('dashboard-group.edit', $dashboardgroup->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $dashboardgroup->id }}</td>
                    </tr>
                    <tr><th> User Id </th><td> {{ $dashboardgroup->user_id }} </td></tr><tr><th> Code </th><td> {{ $dashboardgroup->code }} </td></tr><tr><th> Name </th><td> {{ $dashboardgroup->name }} </td></tr><tr><th> Hive Ids </th><td> {{ $dashboardgroup->hive_ids }} </td></tr><tr><th> Speed </th><td> {{ $dashboardgroup->speed }} </td></tr><tr><th> Interval </th><td> {{ $dashboardgroup->interval }} </td></tr><tr><th> Show Inspections </th><td> {{ $dashboardgroup->show_inspections }} </td></tr><tr><th> Show All </th><td> {{ $dashboardgroup->show_all }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
