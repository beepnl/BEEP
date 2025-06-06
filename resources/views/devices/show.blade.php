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
        <div class="col-xs-12 col-sm-4 col-md-2">
            <div class="form-group">
                <label>{{ __('crud.type') }}:</label>
                <p><label class="label label-default">{{ $item->type }}</label></p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-2">
            <div class="form-group">
                <label>RTC:</label>
                <p><label class="label label-default">{{ $item->rtc }}</label></p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4 col-md-2">
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
                    <table id="table-flashlogs" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Upload date</th>
                                <th>Last re-parsed</th>
                                <th>Hive</th>
                                <th>Messages</th>
                                <th>Time %</th>
                                <th>Persisted days</th>
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
                                <td>{{ round($fl->time_percentage) }}</td>
                                <td>{{ $fl->persisted_days}}</td>
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
                    <div class="col-xs-12 col-sm-4 col-md-2">
                        <div class="form-group">
                            <label>Min # of DB matches:</label>
                            {!! Form::number('matches_min', $matches_min, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-2">
                        <div class="form-group">
                            <label>Min # of match properties:</label>
                            {!! Form::number('match_props', $match_props, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-2">
                        <div class="form-group">
                            <label>Number of DB records to query:</label>
                            {!! Form::number('db_records', $db_records, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-2">
                        <div class="form-group">
                            <label>Save Flashlog result after parsing</label>
                            <br>
                            {!! Form::checkbox('save_result', 1, $save_result) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-4 col-md-2">
                        <div class="form-group">
                            <label>Force NOT using RTC</label>
                            <br>
                            {!! Form::checkbox('dont_use_rtc', 1, $dont_use_rtc) !!}
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>">Re-parse Flashlog {{ $flashlog->id }} with current variables</button>
                    </div>
                    {!! Form::close() !!}



                    @if (isset($log))

                    <script type="text/javascript">
                        $(document).ready(function() {
                            $("#table-blocks").DataTable(
                            {
                                "pageLength": 50,
                                "language": 
                                    @php
                                        echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                                    @endphp
                                ,
                                "order": 
                                [
                                    [ 0, "asc" ]
                                ],
                            });
                        });

                    </script>

                    <div class="col-xs-12">
                        <hr>
                        <h4>
                            Time match: {{ $log['time_percentage'] }}, Weight match: {{ $log['weight_percentage'] }}, On/off blocks: {{ count($log['log']) }}, Lines: {{ $log['lines_received'] }}, Messages: {{ $log['log_messages'] }} 
                        </h4>
                    </div>
                    <table id="table-blocks" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>FW version</th>
                                <th>Line # Start-end / rows</th>
                                <th>DB rows / % of log</th>
                                <th>DB request from</th>
                                <th>Length (days)</th>
                                <th>Interval : send ratio (min)</th>
                                <th>Start time match</th>
                                <th>End time match</th>
                                <th>Matches / Number of measurements</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($log['log'] as $bl)
                                @php 
                                    $interv_min = isset($bl['interval_min']) ? max(1, $bl['interval_min']) : null; // min
                                    $interv_rat = isset($bl['transmission_ratio']) ? max(1, $bl['transmission_ratio']) : null; // % 
                                    $interval_m = isset($interv_min) && isset($interv_rat) ? $interv_min * $interv_rat : null;
                                    $data_rows  = $bl['end_i'] - $bl['start_i'];
                                    $db_row_cnt = isset($bl['dbCount']) ? $bl['dbCount'] : 0;
                                    $db_row_perc= $data_rows > 0 ? round(100 * $db_row_cnt / $data_rows) : 0;
                                    $meas_p_day = null;
                                    $data_days  = null;

                                    // row color
                                    if (isset($bl['matches']['matches']) && $db_row_perc < 90)
                                        $td_attr = ' style="color:#007700"';
                                    else if (isset($bl['matches']['matches']) && $db_row_perc > 0)
                                        $td_attr = ' style="color:#AA0000"';
                                    else
                                        $td_attr = ' style="color:#999"';

                                    // interval calculation
                                    if ($interval_m && $interval_m > 0)
                                    {
                                        $meas_p_day = round(24 * 60 / $interval_m);
                                        $data_days  = round($bl['duration_hours']/24, 1);
                                    }
                                @endphp
                                <tr>
                                    <td {!! $td_attr !!}>{{ $bl['block'] }}</td>
                                    <td {!! $td_attr !!}>{{ isset($bl['fw_version']) ? $bl['fw_version'] : '' }}</td>
                                    <td {!! $td_attr !!}>{{ $bl['start_i'] }}->{{ $bl['end_i'] }}<br>={{ $data_rows }} rows</td>
                                    <td {!! $td_attr !!}>{{ $db_row_cnt }}<br><span style="font-weight: bold;">={{ $db_row_perc }}% in DB</span></td>
                                    <td {!! $td_attr !!}>{{ isset($bl['db_time']) ? $bl['db_time'] : '-' }}</td>
                                    <td {!! $td_attr !!}>{{ $data_days }}<br>{{ $meas_p_day }} p/day</td>
                                    <td {!! $td_attr !!}>{{ $interval_m }} (={{ $interv_min }} x {{ $interv_rat}})</td>
                                    <td {!! $td_attr !!}>{{ isset($bl['time_start']) ? $bl['time_start'] : '-' }}</td>
                                    <td {!! $td_attr !!}>{{ isset($bl['time_end']) ? $bl['time_end'] : '-' }}</td>
                                    <td {!! $td_attr !!}>
                                        <div style="font-size: 10px;">
                                        @if (isset($bl['matches']['message'])) 
                                            {{ $bl['matches']['message'] }}
                                        @elseif (isset($bl['matches']['matches']))
                                            @foreach($bl['matches']['matches'] as $i => $match)
                                            <div style="width: 200px; display: inline-block; font-size: 11px;">
                                                <h5>Match i={{ $i }}:</h5>
                                                <ol>
                                                    @foreach($match as $par => $val)
                                                    <li><span style="{{ $par != 'time' ? 'width:100px; text-align: right;' : ''}} display: inline-block;">{{ $par }}:</span> {{ $val }}</li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                        </div>
                                    </td>
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