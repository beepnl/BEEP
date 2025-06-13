@extends('layouts.app')
 
@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' (ID: '.$research->id.')' }} Device data completeness
@endsection

@section('content')

            
    @component('components/box')
        @slot('title')
            Select users, devices, and/or dates to show device data
        @endslot

        @slot('action')
        @endslot

        @slot('$bodyClass')
        @endslot

        @slot('body')
        <div class="col-xs-12">
            <form method="GET" action="{{ route('research.data',$research->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="col-xs-12">
                        <div class="form-group {{ $errors->has('user_ids') ? 'has-error' : ''}}">
                            <label for="user_ids" control-label>{{ 'Select consented users' }} ({{ count($consent_users_selected) }} / {{ count($consent_users_select) }})</label>
                            <div>
                                {!! Form::select('user_ids[]', $consent_users_select, $consent_users_selected, array('id'=>'user_ids','class' => 'form-control select2', 'multiple')) !!}
                                {!! $errors->first('user_ids', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-8">
                    <div class="row">
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('device_ids') ? 'has-error' : ''}}">
                                <label for="device_ids" control-label>{{ 'Devices filter (default: all)' }}</label>
                                <div>
                                    {!! Form::select('device_ids[]', $devices_select, $device_ids, array('id'=>'device_ids','class' => 'form-control select2', 'multiple')) !!}
                                    {!! $errors->first('device_ids', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
                        </div>
                      
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('date_start') ? 'has-error' : ''}}">
                                <label for="date_start" control-label>{{ 'From date (filters time based data)' }}</label>
                                <div>
                                    <input class="form-control" name="date_start" type="date" id="date_start" min="{{substr($research->start_date, 0, 10)}}" max="{{substr($research->end_date, 0, 10)}}" value="{{ isset($date_start) ? substr($date_start, 0, 10) : '' }}" >
                                    {!! $errors->first('date_start', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('date_until') ? 'has-error' : ''}}">
                                <label for="date_until" control-label>{{ 'Until date (filters time based data)' }}</label>
                                <div>
                                    <input class="form-control" name="date_until" type="date" id="date_until" min="{{substr($research->start_date, 0, 10)}}" max="{{substr($research->end_date, 0, 10)}}" value="{{ isset($date_until) ? substr($date_until, 0, 10) : '' }}" >
                                    {!! $errors->first('date_until', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('add_flashlogs') ? 'has-error' : ''}}">
                                <label for="add_flashlogs" control-label>{{ 'Show flashlogs' }}</label>
                                <div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="add_flashlogs" type="radio" value="1" @if (isset($add_flashlogs)) {{ (isset($add_flashlogs) && $add_flashlogs == 1) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
                                    </div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="add_flashlogs" type="radio" value="0" @if (isset($add_flashlogs)) {{ ($add_flashlogs == 0) ? 'checked' : '' }} @endif> No</label>
                                    </div>
                                    {!! $errors->first('add_flashlogs', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
                        </div>
                        @if($add_flashlogs)
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('until_last_fl') ? 'has-error' : ''}}">
                                <label for="until_last_fl" control-label>{{ 'Show complete until' }}</label>
                                <div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="until_last_fl" type="radio" value="1" @if (isset($until_last_fl)) {{ (isset($until_last_fl) && $until_last_fl == 1) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Last Flashlog</label>
                                    </div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="until_last_fl" type="radio" value="0" @if (isset($until_last_fl)) {{ ($until_last_fl == 0) ? 'checked' : '' }} @endif> Today</label>
                                    </div>
                                    {!! $errors->first('until_last_fl', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('invalid_log_prognose') ? 'has-error' : ''}}">
                                <label for="invalid_log_prognose" control-label>{{ 'Show flashlog type' }}</label>
                                <div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="invalid_log_prognose" type="radio" value="1" {{ (isset($invalid_log_prognose) && $invalid_log_prognose == 1) ? 'checked' : '' }} > All</label>
                                    </div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="invalid_log_prognose" type="radio" value="0" @if (isset($invalid_log_prognose)) {{ ($invalid_log_prognose == 0) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Only validated</label>
                                    </div>
                                    {!! $errors->first('invalid_log_prognose', '<p class="help-block">:message</p>') !!}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
            </div>

            <div class="col-xs-12">
                <div class="form-group">
                    <br>
                    <button class="btn btn-primary btn-block loading-spinner" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i> Reload data completeness table</button>
                </div>
            </div>
            
            </form>
        </div>

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-devices").DataTable(
                {
                    "pageLength": 10,
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 2, "asc" ]
                    ],
                });
            });

        </script>

        <style type="text/css">
            .tb-row-normal{
                height: 45px; 
                max-height: 45px; 
                min-height: 45px; 
                overflow: hidden; 
                white-space: nowrap;
                width: 200px;
            }
            .tb-row-small{
                height: 45px; 
                max-height: 45px; 
                min-height: 45px; 
                overflow: hidden; 
                white-space: nowrap;
                max-width: 200px;
            }
            .tb-row-very-small{
                height: 45px; 
                max-height: 45px; 
                min-height: 45px; 
                overflow: hidden; 
                white-space: nowrap;
                max-width: 160px;
            }
            .table-header-rotated {
                margin:0;
            }
            .table-header-rotated thead > tr > th{
                height: 82px;
                border-right: 1px solid #AAA;
                border-bottom: 2px solid #999;
                border-top: 1px solid #999 !important;
            }
            .table-header-rotated thead > tr > th:first-child{
                border-left: 1px solid #AAA;
            }
            .table-header-rotated thead th.rotate > div > div{
                width: 70px;
                height: 10px;
            }
            .table-header-rotated thead th.rotate > div {
                -webkit-transform: translate(-2px, -7px) rotate(270deg);
                transform: translate(-2px, -7px) rotate(270deg);
                height: 10px;
                width: 10px;
                margin: 0px;
                padding: 0px;
            }
            .table-header-rotated > tbody > tr > th{
                border-left: 1px solid #AAA;
            }
            .table-header-rotated > tbody > tr > td{
                width: 10px;
                height: 45px;
            }
            .rd{
                background-color: #F8DADA;
            }
            .or{
                background-color: #F9E39B;
            }
            .gr{
                background-color: #B5E989;
            }
            td.prognose{
                border: 2px dashed green;
            }
            td.error{
                border: 2px solid red;
            }
            td.flashlog{
                border-bottom: 3px solid black;
            }
            td.lowbat{
                border-top: 3px solid red !important;
            }
            td.arrow-left {
              position: relative;
            }

            td.arrow-left::before {
              content: "";
              position: absolute;
              top: 100%;
              left: 0px;
              transform: translateY(-100%);
              width: 0;
              height: 0;
              border-top: 8px solid transparent;
              border-bottom: 8px solid transparent;
              border-left: 10px solid #333; /* kleur van de pijl */
            }
            td.explain{
                border-top: 1px solid grey !important;
                border-right: 1px solid grey;
            }
            button i {
              pointer-events: none;
            }
        </style>

        <script>
            
            @php
                $query_add_arr = [];
                $not_arr       = ['log_device_id', 'only_change_value', 'only_change', 'log_device_note'];
                foreach(request()->query() as $qp => $qv)
                {
                    if(in_array($qp, $not_arr))
                        continue;

                    if (is_array($qv))
                    {
                        foreach($qv as $v)
                            $query_add_arr[] = $qp."[]=".$v;
                    }
                    else
                    {
                        $query_add_arr[] = "$qp=$qv";
                    }

                }
                $query_add_js = implode('&', $query_add_arr);
                $i = 0;
            @endphp

            function handleKey(event) {
                if ((event.type === "keydown" && event.key === "Enter") || event.type === "click") {
                  event.preventDefault(); // Prevent default form submission

                  const change_value   = typeof event.target.dataset.changeValue != 'undefined' ? event.target.dataset.changeValue : event.target.value;
                  const device_id      = event.target.dataset.deviceId;
                  const only_change    = event.target.dataset.onlyChange;

                  //console.log(`New ${only_change} for device ${device_id}: ${change_value}`);

                  if (typeof device_id != 'undefined' && typeof only_change != 'undefined' && typeof change_value != 'undefined')
                    window.location.href = "{{ route('research.data', $research->id) }}?log_device_id="+device_id+"&only_change_value="+change_value+"&only_change="+only_change+"&{!! $query_add_js !!}";
                }
            }

        </script>


        <div class="col-xs-12">
            
            <div style="vertical-align: bottom;">
                <div style="display: inline-block; width: 50%;">
                    <h2 style="margin-top: 20px;">Device data completeness per day (%)</h2>
                </div>
                <div style="display: inline-block; width: 40%; vertical-align: bottom;">
                    <span>Legend table visualizations</span>
                    <table class="table table-header-rotated" style="border: 1px solid grey;">
                        <tbody>
                            <tr class="tb-row-small">
                                <td class="tb-row-small explain flashlog flashlog arrow-left">Flashlog<br>upload date</td>
                                <td class="tb-row-small explain flashlog">Flashlog<br>available</td>
                                <td style="border-top: 2px dashed green;" class="tb-row-small flashlog prognose">Flashlog data<br>prognose %</td>
                                <td class="tb-row-small lowbat">Low<br>battery</td>
                                <td class="tb-row-small explain gr">>80% db<br>data</td>
                                <td class="tb-row-small explain or">>40% db<br>data</td>
                                <td class="tb-row-small explain rd">=<40% db<br>data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <small>Data completeness = Percentage of maximum available weight data points per day per device (for the set mesurement interval, which defaults to 15 min = 96 data points per day). {{ $add_flashlogs ? 'Taking' : 'Not taking' }} into account prognose for available validated Flashlogs</small>
            <!-- Data table -->

            <div style="display: block;">
            <div style="display: inline-block; width: 750px; overflow-y: hidden; overflow-x: scroll;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            <th class="rotate">#</th>
                            <th class="rotate">Device</th>
                            <th class="rotate">Current Apiary</th>
                            <th class="rotate">Current hive</th>
                            <th class="rotate" title="Average data completeness of the {{ $data_completeness_count }} selected devices and dates"><span style="color:grey;">{{ empty($data_completeness) ?  '' : $data_completeness.'%' }}</span><br><br>Data</th>
                            @if($add_flashlogs)
                            <th class="rotate">CSV export</th>
                            <th class="rotate">Flashlogs</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices_show as $device)
                        @php
                            $loc_id      = $device->getLocationIdAttribute();
                            $data_points = isset($totals['devices'][$device->key]['total']) ? $totals['devices'][$device->key]['total'].' weight data points' : '';
                            $data_comp   = isset($totals['devices'][$device->key]['data_completeness']) ? $totals['devices'][$device->key]['data_completeness'].'%' : '';
                            $data_days_dev = isset($totals['devices'][$device->key]['data_days']) ? $totals['devices'][$device->key]['data_days'] : $data_days;
                            $form_q_html = "";
                            foreach(array_merge(request()->query(), ['log_device_id'=>$device->id]) as $key => $value)
                            {
                                if ($key == 'log_device_id' || !in_array($key, $not_arr))
                                {
                                    if (is_array($value))
                                    {
                                        foreach($value as $v)
                                            $form_q_html .= '<input type="hidden" name="'.$key.'[]" value="'.$v.'">';
                                    }
                                    else
                                    {
                                        $form_q_html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                                    }
                                }
                            }
                            $log_status = !isset($device->log_file_info['valid']) ? 'fa-question' : (boolval($device->log_file_info['valid']) ? 'fa-check' : 'fa-times');
                            $log_color  = !isset($device->log_file_info['valid']) ? '' : (boolval($device->log_file_info['valid']) ? 'gr' : 'rd');
                            $i++;
                        @endphp
                        <tr class="tb-row-small" @if (isset($device->deleted_at)) style="color: #AAA;" title="Device has been deleted at {{$device->deleted_at}}" @else title="{{ $device->name }}" @endif>
                            <th>{{$i}}</th> 
                            <th @isset($device) title="{{ $device->name }} (id: {{ $device->id }} created: {{ $device->created_at }})" @endisset class="tb-row-very-small row-header">{{ $device->name }} ({{ $device->id }})</th> 

                            <th @if(null !== $device->location()) title="{{ $device->location_name }} (id: {{ $loc_id }} created: {{ $device->location()->created_at }})"@endif class="tb-row-very-small row-header">{{ $device->location_name }} ({{ $loc_id }})</th> 

                            <th @isset($device->hive) title="{{ $device->hive_name }} (id: {{ $device->hive_id }} created: {{ $device->hive->created_at }})"@endisset class="tb-row-very-small row-header">{{ $device->hive_name }} ({{ $device->hive_id }})</th> 

                            <th class="tb-row-very-small row-header" title="Average data completeness: {{$data_comp}} ({{ $data_points }} over {{$data_days_dev}} data days)">{{ $data_comp }}</th> 

                            @if($add_flashlogs)
                            <th class="tb-row-normal row-header" style="padding-top: 0; padding-bottom: 0;">
                                <form id="create-csv-device-{{ $device->id }}" method="GET" action="{{ route('research.data', $research->id) }}" accept-charset="UTF-8" class="form-horizontal">
                                    
                                    <button style="width: 34px;" id="device-log-valid-{{ $device->id }}" data-device-id="{{ $device->id }}" data-only-change="valid" data-change-value="{{ isset($device->log_file_info['valid']) && boolval($device->log_file_info['valid']) ? 0 : 1 }}" onclick="handleKey(event)" class="btn btn-default btn-sm {{$log_color}} loading-spinner" title="Change valid" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa {{$log_status}}" aria-hidden="true"></i></button>
                                    
                                    
                                    
                                    <input id="device-note-{{ $device->id }}" data-device-id="{{ $device->id }}" data-only-change="note" type="text" name="log_device_note" placeholder="Note" value="{{ isset($device->log_file_info['note']) ? $device->log_file_info['note'] : '' }}" onkeydown="handleKey(event)">

                                    {!! $form_q_html !!}
                                    
                                    <button class="btn btn-warning btn-sm loading-spinner" title="Create total CSV" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-upload" aria-hidden="true"></i></button>
                                    
                                    @if(isset($device->log_file_info['csv_url']))
                                        <a href="{{ $device->log_file_info['csv_url'] }}" target="_blank" class="btn btn-success btn-sm" title="Download total CSV: {{ App\Models\CalculationModel::arrayToString($device->log_file_info, ' ', '', ['csv_url','valid_data_points']) }}"><i class="fa fa-download" aria-hidden="true"></i></a>
                                    @endif
                                    
                                </form>
                            </th>

                            <th class="tb-row-normal row-header" style="padding-top: 0; padding-bottom: 0">{!! $device->getFlashLogsHtml($date_start) !!}</th>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 760px); overflow-y: hidden; overflow-x: scroll;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            @foreach($dates as $date => $d)
                                <th class="rotate"><div><div>{{ $date }}</div></div></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices_show as $device)
                        <tr>
                            @php
                                $key = $device->key;
                            @endphp
                            @foreach($dates as $date => $d)
                                @php
                                    $perc      = '';
                                    $class     = '';
                                    $title     = '';
                                    $class_arr = [];
                                    $title_arr = [];

                                    if (isset($d['devices'][$key]['err']))
                                    {
                                        $class_arr[] = 'error';
                                        $title_arr[] = $d['devices'][$key]['err'];
                                    }

                                    if (isset($d['devices'][$key]['bv']))
                                    {
                                        $title_arr[] = 'Bat V='.$d['devices'][$key]['bv'];
                                        if ($d['devices'][$key]['bv'] < 2.9)
                                            $class_arr[] = 'lowbat';
                                    }
                                    
                                    if ($add_flashlogs)
                                    {
                                        if (isset($d['devices'][$key]['flashlog_created']))
                                        {
                                            $class_arr[] = 'arrow-left';
                                            $title_arr[] = 'Flashlog id:'.$d['devices'][$key]['flashlog_created'] ;
                                        }
                                        if (isset($d['devices'][$key]['flashlog_prognose']))
                                        {
                                            $class_arr[] = 'flashlog prognose';
                                            if (!isset($d['devices'][$key]['flashlog_created']))
                                                $title_arr[] = 'Flashlog id:'.$d['devices'][$key]['flashlog_prognose'].'%';
                                        }
                                        else if (isset($d['devices'][$key]['flashlog']))
                                        {
                                            $class_arr[] = 'flashlog'; 
                                            $title_arr[] = 'Flashlog id:'.$d['devices'][$key]['flashlog'];
                                        }

                                    }

                                    if (isset($d['devices'][$key]['perc']))
                                    {
                                        $perc      = $d['devices'][$key]['perc'];
                                        if ($perc >= 80)
                                            $class_arr[] = 'gr';
                                        else if ($perc >= 40)
                                            $class_arr[] = 'or';
                                        else
                                            $class_arr[] = 'rd';
                                    }

                                    if (count($class_arr) > 0)
                                        $class = implode(' ', $class_arr);

                                    if (count($title_arr) > 0)
                                        $title = 'title="'.implode('&#10;', $title_arr).'"';
                                @endphp
                                <td class="tb-row-small {{ $class }}" {!! $title !!}>{{ $perc }}</td>
                            @endforeach
                        </tr>
                       @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>

        @endslot
    @endcomponent

@endsection