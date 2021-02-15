@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.Alert')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.Alert')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('alert.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.Alert')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-alert").DataTable(
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


        <table id="table-alert" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Alert Rule</th><th>Alert Function</th><th>Alert Value</th><th>Measurement (Id)</th><th>Show</th><th>Location Name</th><th>Hive Name</th><th>Device Name</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($alert as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->alert_rule->name }}</td><td>{{ $item->alert_function }}</td><td>{{ $item->alert_value }}</td><td>{{ $item->measurement->pq_name_unit }} ({{ $item->measurement->id }})</td><td>{{ $item->show }}</td><td>{{ $item->location_name }}</td><td>{{ $item->hive_name }}</td><td>{{ $item->device_name }}</td>
                    <td col-sm-1>
                        <a href="{{ route('alert.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('alert.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('alert.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'Alert','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $alert->render() !!} </div>

        @endslot
    @endcomponent
@endsection
