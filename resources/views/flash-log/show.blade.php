@extends('layouts.app')

@section('page-title') {{ __('beep.FlashLog').': '.(isset($flashlog->name) ? $flashlog->name : __('general.Item')).' ('.$flashlog->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($flashlog->name) ? $flashlog->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('flash-log.edit', $flashlog->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $flashlog->id }}</td>
                    </tr>
                    <tr>
                        <th> Created at </th>
                        <td> {{ $flashlog->created_at }} </td>
                    </tr>
                    <tr>
                        <th> Updated at </th>
                        <td> {{ $flashlog->updated_at }} </td>
                    </tr>
                    <tr>
                        <th> Log date start </th>
                        <td> {{ $flashlog->log_date_start }}</td>
                    </tr>
                    <tr @if($flashlog->validLog()) style="background-color: lightgreen;"@endif>
                        <th> Log date end </th>
                        <td >{{ $flashlog->log_date_end }}</td>
                    </tr>
                    <tr @if($flashlog->validLog()) style="background-color: lightgreen;"@endif>
                        <th> Log data per day </th>
                        <td>{{ $flashlog->logs_per_day }}</td>
                    </tr>
                    <tr>
                        <th> Log days </th>
                        <td> {{ $flashlog->getLogDays() }}</td>
                    </tr>
                    <tr>
                        <th> User </th>
                        <td> @isset($flashlog->user_id) {{ $flashlog->user_name }} ({{ $flashlog->user_id }}) @endisset </td>
                    </tr>
                    <tr>
                        <th> Device </th>
                        <td> 
                            @isset($flashlog->device_id)
                            <a href="/devices/{{ $flashlog->device_id }}">{{ isset($flashlog->device_name) ? $flashlog->device_name : 'NAME?' }}</a>.
                            Go to <a href="/sensordefinition?device_id={{ $flashlog->device_id }}"> Sensor definitions</a>
                            @endisset 
                        </td>
                    </tr>
                    <tr>
                        <th> Apiary - Hive </th>
                        <td> @isset($flashlog->hive_id) {{ $flashlog->hive->location }} ({{ $flashlog->hive->location_id }}) - {{ $flashlog->hive_name }} ({{ $flashlog->hive_id }}) @endisset </td>
                    </tr>
                    <tr>
                        <th> Log Messages </th>
                        <td> {{ $flashlog->log_messages }} </td>
                    </tr>
                    <tr>
                        <th> Log Saved / Erased / Parsed / Has time</th>
                        <td> {{ $flashlog->log_saved }} / {{ $flashlog->log_erased }} / {{ $flashlog->log_parsed }} / {{ $flashlog->log_has_timestamps }} </td>
                    </tr>
                    <tr>
                        <th> Bytes Received </th>
                        <td> {{ $flashlog->bytes_received }} </td>
                    </tr>
                    <tr>
                        <th> Bytes at BEEP base </th>
                        <td> {{ $flashlog->log_size_bytes }} </td>
                    </tr>
                    <tr>
                        <th> Log file raw </th>
                        <td> <a target="_blank" href="{{ $flashlog->log_file }}">{{ $flashlog->log_file }}</a> </td>
                    </tr>
                    <tr>
                        <th> Log file stripped </th>
                        <td> <a target="_blank" href="{{ $flashlog->log_file_stripped }}">{{ $flashlog->log_file_stripped }}</a> </td>
                    </tr>
                    <tr>
                        <th> Log file parsed </th>
                        <td>
                            <a target="_blank" href="{{ $flashlog->log_file_parsed }}">{{ $flashlog->log_file_parsed }}</a> 
                            <a href="{{ route('flash-log.parse', ['id'=>$flashlog->id, 'load_show'=>1]) }}" title="{{ __('crud.parse') }}"><button title="Parse Flashlog with time fill and adding Sensordefinitions (slow)" class="btn btn-info loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
                            <a href="{{ route('flash-log.parse', ['id'=>$flashlog->id, 'add_meta'=>1, 'load_show'=>1] ) }}" title="{{ __('crud.parse') }}"><button title="Add meta data" class="btn btn-sm btn-warning loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
                            <a href="{{ route('flash-log.parse', ['id'=>$flashlog->id, 'csv'=>1, 'load_show'=>1] ) }}" title="{{ __('crud.parse') }}"><button title="Create new CSV" class="btn btn-sm btn-success loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-table" aria-hidden="true"></i></button></a>
                        </td>
                    </tr>
                    <tr>
                        <th> Log file CSV </th>
                        <td> <a target="_blank" href="{{ $flashlog->csv_url }}">{{ $flashlog->csv_url }}</a> </td>
                    </tr>

                    <tr>
                        <th> Log meta </th>
                        <td>
<textarea rows="10" style="width: 100%">
{!! json_encode($flashlog->meta_data, JSON_PRETTY_PRINT) !!}
</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
