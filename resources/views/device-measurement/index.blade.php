@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.DeviceMeasurement')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.DeviceMeasurement')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('device-measurement.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.DeviceMeasurement')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-device-measurement").DataTable(
                    {
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 1, "asc" ]
                    ],
                });
            });
        </script>


        <table id="table-device-measurement" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Inside</th>
                    <th>Zero Value</th>
                    <th>Unit Per Value</th><th>Measurement Id</th><th>Physical Quantity Id</th><th>Sensor Id</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($devicemeasurement as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->inside }}</td>
                    <td>{{ $item->zero_value }}</td>
                    <td>{{ $item->unit_per_value }}</td><td>{{ $item->measurement_id }}</td><td>{{ $item->physical_quantity_id }}</td><td>{{ $item->sensor_id }}</td>
                    <td col-sm-1>
                        <a href="{{ route('device-measurement.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('device-measurement.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('device-measurement.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'DeviceMeasurement','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $devicemeasurement->render() !!} </div>

        @endslot
    @endcomponent
@endsection
