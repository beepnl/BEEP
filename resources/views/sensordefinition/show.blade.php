@extends('layouts.app')

@section('page-title') {{ __('beep.SensorDefinition').': '.(isset($sensordefinition->name) ? $sensordefinition->name : __('general.Item')).' ('.$sensordefinition->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($sensordefinition->name) ? $sensordefinition->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('sensordefinition.edit', $sensordefinition->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $sensordefinition->id }}</td>
                    </tr>
                    <tr><th> Zero Value </th><td> {{ $sensordefinition->offset }} </td></tr><tr><th> Unit Per Value </th><td> {{ $sensordefinition->multiplier }} </td></tr><tr><th> Measurement Id </th><td> {{ $sensordefinition->measurement_id }} </td></tr><tr><th> Physical Quantity Id </th><td> {{ $sensordefinition->physical_quantity_id }} </td></tr><tr><th> Sensor Id </th><td> {{ $sensordefinition->device_id }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
