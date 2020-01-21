@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.SensorDefinition')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.SensorDefinition')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('sensordefinition.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.SensorDefinition')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-sensordefinition").DataTable(
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


        <table id="table-sensordefinition" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Inside</th>
                    <th>Zero Value</th>
                    <th>Unit Per Value</th>
                    <th>Measurement in</th>
                    <th>Measurement out</th>
                    <th>Sensor</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sensordefinition as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->inside }}</td>
                    <td>{{ $item->offset }}</td>
                    <td>{{ $item->multiplier }}</td>
                    <td>{{ isset($item->input_measurement) ? $item->input_measurement->abbreviation : '-' }}</td>
                    <td>{{ isset($item->output_measurement) ? $item->output_measurement->abbreviation : '-'  }}</td>
                    <td>{{ isset($item->device) ? $item->device->name : '-'  }}</td>
                    <td col-sm-1>
                        <a href="{{ route('sensordefinition.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('sensordefinition.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('sensordefinition.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'sensordefinition','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $sensordefinition->render() !!} </div>

        @endslot
    @endcomponent
@endsection
