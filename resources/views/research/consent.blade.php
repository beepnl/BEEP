@extends('layouts.app')

@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' (ID: '.$research->id.')' }} Research dates: {{ substr($research->start_date, 0, 10) }} - {{ substr($research->end_date, 0, 10) }}
    @permission('role-view')
        <a href="{{ route('research.show', $research->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-primary pull-right"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
    @endpermission
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>'Research consents']) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('research.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add_a', ['item'=>__('beep.Research')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
            table-responsive
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-research").DataTable(
                    {
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 0, "asc" ],
                        [ 4, "asc" ]
                    ]
                });
            });
        </script>


        <table id="table-research" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th col-xs-1>User</th>
                    <th col-xs-1>Consent ID</th>
                    <th col-xs-2>Consent</th>
                    <th col-xs-2>Created date</th>
                    <th col-xs-2>Updated date</th>
                    <th col-xs-2>Hives</th>
                    <th col-xs-2>Devices</th>
                    <th col-xs-2>Locations</th>
                    <th style="min-width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($consents as $item)
                <tr>
                    {{-- <td>{{ $loop->iteration or $item->id }}</td> --}}
                    <td>{{ $item->user_id }}<br>{{ $item->user_name }}</td>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->consent }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->updated_at }}</td>
                    <td style="word-break: break-word;">{{ $item->consent_hive_ids }}</td>
                    <td style="word-break: break-word;">{{ $item->consent_sensor_ids }}</td>
                    <td style="word-break: break-word;">{{ $item->consent_location_ids }}</td>
                    <td>
                        @if($item->consent == 1)
                        <a href="{{ route('research.consent_edit', ['id'=>$research->id, 'c_id'=>$item->id]) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
                        @endif

                        <form method="POST" action="{{ route('research.consent_edit', ['id'=>$research->id, 'c_id'=>$item->id, 'delete'=>1]) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('PATCH') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'Consent','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @endslot
    @endcomponent
@endsection
