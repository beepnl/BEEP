@extends('layouts.app')

@section('page-title') {{ __('beep.DeviceMeasurement').': '.(isset($devicemeasurement->name) ? $devicemeasurement->name : __('general.Item')).' ('.$devicemeasurement->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($devicemeasurement->name) ? $devicemeasurement->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('device-measurement.edit', $devicemeasurement->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $devicemeasurement->id }}</td>
                    </tr>
                    <tr><th> Zero Value </th><td> {{ $devicemeasurement->zero_value }} </td></tr><tr><th> Unit Per Value </th><td> {{ $devicemeasurement->unit_per_value }} </td></tr><tr><th> Measurement Id </th><td> {{ $devicemeasurement->measurement_id }} </td></tr><tr><th> Physical Quantity Id </th><td> {{ $devicemeasurement->physical_quantity_id }} </td></tr><tr><th> Sensor Id </th><td> {{ $devicemeasurement->sensor_id }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
