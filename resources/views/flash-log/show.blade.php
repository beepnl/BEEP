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

            @php
                $valid = $flashlog->validLog();
                $manual= $flashlog->valid_override === 1 ? '(<i class="fa fa-sm fa-exclamation-triangle"></i> manual override)' : '';
                $error = $flashlog->log_date_end > $flashlog->created_at ? true : false;
                $color = $error ? 'red' : ($valid ? 'darkgreen' : null);
                $msg   = $error ? 'End date after upload date' : ($valid ? 'Validated log' : null);

                $invalid_meta_data = [];
                if (isset($flashlog->meta_data['valid_data_points']))
                {
                    foreach($flashlog->meta_data['valid_data_points'] as $d => $logs_day)
                    {
                        if (is_numeric($logs_day) && $logs_day > 0 && $logs_day != 96)
                            $invalid_meta_data[$d] = "$d: $logs_day";
                    }
                }

                $logs_per_day      = $flashlog->getLogPerDay();
                $logs_per_day_full = isset($flashlog->device) ? $flashlog->device->getMeasurementsPerDay() : 96;
                $logs_per_day_perc = max(0, min(100, round(100 * $logs_per_day / $logs_per_day_full, 1)));
                $time_percentage   = $flashlog->getTimeLogPercentage();
                $time_color        = $logs_per_day_perc >= env('FLASHLOG_VALID_TIME_LOG_PERC', 90) && $logs_per_day_perc <= 101 ? 'darkgreen' : 'red';

                $weight_kg_perc    = $flashlog->getWeightLogPercentage();
                $errors            = $flashlog->getErrorsArray();
                $fixes             = $flashlog->getFixesArray();

            @endphp

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th style="text-align: right;"> Updated at </th>
                        <td> {{ $flashlog->updated_at }} </td>
                        <th style="text-align: right;"> Created at (upload date)</th>
                        <td> {{ $flashlog->created_at }} </td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Log date start </th>
                        <td> {{ $flashlog->log_date_start }}</td>
                        <th style="text-align: right; @isset($color) color: {{$color}}; font-weight: bold; @endisset" title="{{$msg}}"> Log date end </th>
                        <td style="@isset($color) color: {{$color}}; font-weight: bold; @endisset">{{ $flashlog->log_date_end }}</td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Log days </th>
                        <td> {{ $flashlog->getLogDays() }} (of which {{ $weight_kg_perc }}% weight)</td>
                        <th style="text-align: right; @if($logs_per_day == 96) color: darkgreen; font-weight: bold; @endif"> Log data per day </th>
                        <td >{{ $logs_per_day }}</td>
                    </tr>
                    <tr style="color: {{$time_color}}; font-weight: bold;">
                        <th style="text-align: right;"> Time log percentage </th>
                        <td >{{$time_percentage}}% = 100 * Logs per day ({{ $logs_per_day }}) / logs_per_day_full ({{ $logs_per_day_full }})</td>
                        <th style="color: @if($valid) darkgreen @else red @endif; font-weight: bold; text-align: right;" > Validated</th>
                        <td style="color: @if($valid) darkgreen @else red @endif;">@if($valid) Yes @else No @endif {!! $manual !!}</td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Errors </th>
                        <td>
                            @foreach($errors as $icon => $err)
                            <span class="label label-danger"><i class="fa fa-sm {{$icon}}"></i> {{ $err }}</span>
                            @endforeach
                        </td>
                        <th style="text-align: right;"> Fixes </th>
                        <td>
                            @foreach($fixes as $icon => $fix)
                            <span class="label label-success"><i class="fa fa-sm {{$icon}}"></i> {{ $fix }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> User </th>
                        <td> @isset($flashlog->user_id) {{ $flashlog->user_name }} ({{ $flashlog->user_id }}) @endisset </td>
                        <th style="text-align: right;"> Device </th>
                        <td> 
                            @isset($flashlog->device_id)
                            <a href="/devices/{{ $flashlog->device_id }}">{{ isset($flashlog->device_name) ? $flashlog->device_name : 'NAME?' }}</a>.
                            Go to <a href="/sensordefinition?device_id={{ $flashlog->device_id }}"> Sensor definitions</a>
                            @endisset 
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Apiary - Hive </th>
                        <td> @isset($flashlog->hive_id) {{ $flashlog->hive->location }} ({{ $flashlog->hive->location_id }}) - {{ $flashlog->hive_name }} ({{ $flashlog->hive_id }}) @endisset </td>
                        <th style="text-align: right;"> Log Messages </th>
                        <td> {{ $flashlog->log_messages }} </td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Log Saved / Erased / Parsed / Has time</th>
                        <td> {{ $flashlog->log_saved }} / {{ $flashlog->log_erased }} / {{ $flashlog->log_parsed }} / {{ $flashlog->log_has_timestamps }} </td>
                        <th style="text-align: right;"> Bytes Received </th>
                        <td> {{ $flashlog->bytes_received }} vs Bytes at BEEP base: {{ $flashlog->log_size_bytes }}
                        </td>
                    </tr>
                   
                    <tr>
                        <th style="text-align: right;"> Log file re-parse options </th>
                        <td>
                            <a href="{{ route('flash-log.parse', ['id'=>$flashlog->id, 'load_show'=>1, 'correct_data'=>$correct_data]) }}">
                                <button title="Parse Flashlog with time fill and adding Sensordefinitions (slow)" class="btn btn-info loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i>
                                </button>
                            </a>
                            <a href="{{ route('flash-log.parse', ['id'=>$flashlog->id, 'add_meta'=>1, 'load_show'=>1, 'correct_data'=>$correct_data] ) }}">
                                <button title="Add meta data" class="btn btn-warning loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i>
                                </button>
                            </a>
                            <a href="{{ route('flash-log.parse', ['id'=>$flashlog->id, 'csv'=>1, 'load_show'=>1, 'correct_data'=>$correct_data] ) }}">
                                <button title="Create new CSV" class="btn btn-success loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-table" aria-hidden="true"></i>
                                </button>
                            </a>

                            <a href="{{ route('devices.flashlog', ['id'=>$flashlog->device_id, 'fl_id'=>$flashlog->id, 'dont_use_rtc'=>1] ) }}">
                                <button title="Parse Flashlog without RTC" class="btn btn-danger loading-spinner" data-loading-text="<i class='fa fa-clock-o fa-spin'></i>">
                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                                </button>
                            </a>
                        
                        <form action="{{ route('flash-log.show', $flashlog->id) }}" method="GET">
                            <th style="text-align: right;">
                                <label for="correct_data">Correct data? (time changes)</label>
                            </th>
                            <td>
                                <select name="correct_data" onchange="this.form.submit()" id="correct_data" class="form-control">
                                    <option value="0" {{ $correct_data == '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $correct_data == '1' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </td>
                        </form>
                    </tr>
                    
                    <tr>
                        <th style="text-align: right;"> Log meta </th>
                        <td>
<textarea name="meta_data" rows="10" style="width: 100%">
@json(isset($flashlog->meta_data) ? $flashlog->meta_data : [], JSON_PRETTY_PRINT)
</textarea>
                        </td>

                    @isset($flashlog->time_corrections)
                        <th style="text-align: right;"> Time corrections </th>
                        <td>
<textarea name="time_corrections" rows="10" style="width: 100%">
@json($flashlog->time_corrections, JSON_PRETTY_PRINT)
</textarea>
                        </td>
                    @else
                        <th colspan="2">
                    @endisset

                    </tr>

                    <form id="flash-log-analysis" action="{{ route('flash-log.show', $flashlog->id) }}" method="GET">
                    <tr>
                        <th style="text-align: right;"> Analyse date </th>
                        <td>
                            <select name="date" class="form-control">
                                <option value="">Select an invalid log date to analyse</option>
                                @foreach($invalid_meta_data as $d => $label)
                                    <option value="{{ $d }}" {{ $date == $d ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <th style="text-align: right;"> 
                            <label for="show_payload">Show payload?</label>
                        </th>
                        <td>
                            <select name="show_payload" id="show_payload" class="form-control" onchange="this.form.submit()" >
                                <option value="0" {{ old('show_payload') == '0' ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('show_payload') == '1' ? 'selected' : '' }}>Yes</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td colspan="3">
                            <button type="submit" style="width: 100%;">Analyse</button>
                        </td>
                    </tr>
                    </form>

                    <tr> 
                        <th style="text-align: right;"> Analysis {{ $date }} </th>
                        <td colspan="3">
                    @isset($date_analysis)
<textarea rows="50" style="width: 100%">
{!! json_encode($date_analysis, JSON_PRETTY_PRINT) !!}
</textarea>
                        </td>
                    </tr>
                    @endisset

                     <tr>
                        <th style="text-align: right;"> Log file raw </th>
                        <td colspan="3"> <a target="_blank" href="{{ $flashlog->log_file }}">{{ $flashlog->log_file }}</a> </td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Log file stripped </th>
                        <td colspan="3"> <a target="_blank" href="{{ $flashlog->log_file_stripped }}">{{ $flashlog->log_file_stripped }}</a> </td>
                    </tr>
                    <tr>
                        <th style="text-align: right;"> Log file parsed </th>
                        <td colspan="3">
                            <a target="_blank" href="{{ $flashlog->log_file_parsed }}">{{ $flashlog->log_file_parsed }}</a> 
                        </td>
                    </tr>

                    <tr>
                        <th style="text-align: right;"> Log file CSV </th>
                        <td colspan="3"> <a target="_blank" href="{{ $flashlog->csv_url }}">{{ $flashlog->csv_url }}</a> </td>
                    </tr>

                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
