@extends('layouts.app')
 
@section('page-title') {{ __('general.Device') }} {{ $item->id }}
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-4 col-md-3">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                <p>{{ $item->name }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-3">
            <div class="form-group">
                <label>{{ __('crud.type') }}:</label>
                <p><label class="label label-default">{{ $item->type }}</label></p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-3">
            <div class="form-group">
                <label>{{ __('crud.created_at') }}:</label>
                <p>{{ $item->created_at }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-3">
            <div class="form-group">
                <label>{{ __('crud.key') }}:</label>
                <p>{{ $item->key }}</p>
            </div>
        </div>
	</div>

    <div class="row">
        <div class="col-xs-12">
            @component('components/box')
                @slot('title') {{ __('beep.Flash_logs') }} @endslot

                @slot('body')
                    <table id="table-sensors" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Upload date</th>
                                <th>Last re-parsed</th>
                                <th>Hive</th>
                                <th>Messages</th>
                                <th>Time %</th>
                                <th>Log erased</th>
                                <th>Log size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->flashLogs()->get() as $fl)
                            <tr>
                                <td><a href="/flash-log/{{$fl->id}}">{{ $fl->id }}</a></td>
                                <td>{{ $fl->created_at }}</td>
                                <td>{{ $fl->updated_at }}</td>
                                <td>{{ isset($fl->hive) ? $fl->hive->name : '' }}</td>
                                <td>{{ $fl->log_messages }}</td>
                                <td>{{ $fl->time_percentage}}</td>
                                <td>{{ $fl->log_erased}}</td>
                                <td>{{ round($fl->bytes_received/1024/1024,3) }}MB @if(isset($fl->log_size_bytes) && $fl->log_size_bytes > 0) ({{ round(100*($fl->bytes_received / $fl->log_size_bytes),1) }}%) @endif </td>
                                <td col-sm-1>
                                    <a href="{{ route('devices.flashlog', ['id'=>$item->id, 'fl_id'=>$fl->id]) }}" title="{{ __('crud.show') }}"><button class="btn btn-default loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endslot
            @endcomponent
        </div>
    </div>

    @if (isset($flashlog))
    <div class="row">
        <div class="col-xs-12">
            @component('components/box')
                @slot('title') Flash log (ID {{ $flashlog->id }}) content 
                @endslot

                @slot('body')
                    {!! Form::open(['method' => 'GET','route' => ['devices.flashlog', $item->id, $flashlog->id]]) !!}
                    <div class="col-xs-12 col-sm-4 col-md-3">
                        <div class="form-group">
                            <label>Min # of DB matches:</label>
                            {!! Form::number('matches_min', $matches_min, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-3">
                        <div class="form-group">
                            <label>Min # of match properties:</label>
                            {!! Form::number('match_props', $match_props, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-3">
                        <div class="form-group">
                            <label>Number of DB records to query:</label>
                            {!! Form::number('db_records', $db_records, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-3">
                        <div class="form-group">
                            <label>Save Flashlog result after parsing</label>
                            <br>
                            {!! Form::checkbox('save_result', 1, $save_result) !!}
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>">Re-parse Flashlog {{ $flashlog->id }} with current variables</button>
                    </div>
                    {!! Form::close() !!}

                    @if (isset($log))
                    <div class="col-xs-12">
                        <hr>
                        <h4>
                            Time match: {{ $log['time_percentage'] }}, Weight match: {{ $log['weight_percentage'] }}, On/off blocks: {{ count($log) }}, Lines: {{ $log['lines_received'] }}, Messages: {{ $log['log_messages'] }} 
                        </h4>
                    </div>
                    <table id="table-sensors" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>FW version</th>
                                <th>Line # Start-end / pointer</th>
                                <th>DB requset from</th>
                                <th>Length (days)</th>
                                <th>Interval (min)</th>
                                <th>Matches / Number of measurements</th>
                                <th>Start time match</th>
                                <th>End time match</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($log['log'] as $bl)
                                @php $td_attr = isset($bl['no_matches']) ? ' style="color:#999"' : '' @endphp
                                <tr>
                                    <td {!! $td_attr !!}>{{ $bl['block'] }}</td>
                                    <td {!! $td_attr !!}>{{ $bl['fw_version'] }}</td>
                                    <td {!! $td_attr !!}>{{ $bl['start_i'] }}-{{ $bl['end_i'] }} / {{ $bl['fl_i'] }}</td>
                                    <td {!! $td_attr !!}>{{ isset($bl['db_time']) ? $bl['db_time'] : '-' }}</td>
                                    <td {!! $td_attr !!}>{{ round($bl['duration_hours']/24) }}</td>
                                    <td {!! $td_attr !!}>{{ $bl['interval_min'] }}</td>
                                    <td {!! $td_attr !!}>
                                        @if (isset($bl['match']['message'])) 
                                            <div style="font-size: 9px;">{{ $bl['match']['message'] }}</div>
                                        @else 
                                            {{ count($bl['matches']).' / '.count(array_values($bl['matches'])[0])-4 }}
                                        @endif
                                    </td>
                                    <td {!! $td_attr !!}>{{ isset($bl['time_start']) ? $bl['time_start'] : '-' }}</td>
                                    <td {!! $td_attr !!}>{{ isset($bl['time_end']) ? $bl['time_end'] : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p>No blocks inside Flashlog</p>
                    @endif
                @endslot
            @endcomponent
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-xs-12">
            <p>No Flashlog available</p>
        </div>
    </div>
    @endif

@endsection