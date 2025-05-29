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
                        </div>

                        <div class="col-xs-12 col-md-4">
                            <div class="form-group {{ $errors->has('add_flashlogs') ? 'has-error' : ''}}">
                                <label for="add_flashlogs" control-label>{{ 'Add flashlogs' }}</label>
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
                            <div class="form-group {{ $errors->has('invalid_log_prognose') ? 'has-error' : ''}}">
                                <label for="invalid_log_prognose" control-label>{{ 'Use non validated flashlogs' }}</label>
                                <div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="invalid_log_prognose" type="radio" value="1" {{ (isset($invalid_log_prognose) && $invalid_log_prognose == 1) ? 'checked' : '' }} > Yes</label>
                                    </div>
                                    <div class="radio" style="display: inline-block;">
                                        <label><input onchange="this.form.submit()" name="invalid_log_prognose" type="radio" value="0" @if (isset($invalid_log_prognose)) {{ ($invalid_log_prognose == 0) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
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
            .table-header-rotated > tbody > tr > td.rd{
                background-color: #F8DADA;
            }
            .table-header-rotated > tbody > tr > td.or{
                background-color: #F9E39B;
            }
            .table-header-rotated > tbody > tr > td.gr{
                background-color: #B5E989;
            }
            td.prognose{
                border: 2px dashed green;
            }
            td.error{
                border: 2px solid red;
            }
        </style>


        <div class="col-xs-12">
            <hr>
            <h2 style="margin-top: 20px;">Device data completeness per day (%)</h2>
            <small>Data completeness = Percentage of maximum available weight data points per day per device (for the set mesurement interval, which defaults to 15 min = 96 data points per day). {{ $add_flashlogs ? 'Taking' : 'Not taking' }} into account prognose for available validated Flashlogs</small>
            <!-- Data table -->

            <div style="display: block;">
            <div style="display: inline-block; width: 550px; overflow-y: hidden; overflow-x: scroll;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            <th class="rotate">Device</th>
                            <th class="rotate">Apiary</th>
                            <th class="rotate">Hive</th>
                            <th class="rotate" title="Average data completeness of the {{ $data_completeness_count }} selected devices and dates"><span style="color:grey;">{{ empty($data_completeness) ?  '' : $data_completeness.'%' }}</span><br><br>Data</th>
                            @if($add_flashlogs)
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
                        @endphp
                        <tr class="tb-row-small" @if (isset($device->deleted_at)) style="color: #AAA;" title="Device has been deleted at {{$device->deleted_at}}" @else title="{{ $device->name }}" @endif>
                            <th title="{{ $device->name }} (id: {{ $device->id }})" class="tb-row-very-small row-header">{{ $device->name }} ({{ $device->id }})</th> 
                            <th title="{{ $device->location_name }} (id: {{ $loc_id }})" class="tb-row-very-small row-header">{{ $device->location_name }} ({{ $loc_id }})</th> 
                            <th title="{{ $device->hive_name }} (id: {{ $device->hive_id }})" class="tb-row-very-small row-header">{{ $device->hive_name }} ({{ $device->hive_id }})</th> 
                            <th class="tb-row-very-small row-header" title="Average data completeness: {{$data_comp}} ({{ $data_points }} over {{$data_days_dev}} data days)">{{ $data_comp }}</th> 
                            @if($add_flashlogs)
                            <th class="tb-row-normal row-header" style="padding-top: 0; padding-bottom: 0">{!! $device->getFlashLogsHtml($date_start) !!}</th>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 560px); overflow-y: hidden; overflow-x: scroll;">
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
                                    $color     = '';
                                    $prognose  = '';
                                    $error     = '';

                                    if (isset($d['devices'][$key]['err']))
                                    {
                                        $error = 'error" title="'.$d['devices'][$key]['err'];
                                    }
                                    else if ($add_flashlogs && isset($d['devices'][$key]['flashlog_prognose']))
                                    {
                                        $prognose = 'prognose" title="Weight data in flashlog '.$d['devices'][$key]['flashlog_prognose'].'%';
                                    }

                                    if (isset($d['devices'][$key]['perc']))
                                    {
                                        $perc      = $d['devices'][$key]['perc'];
                                        if ($perc >= 80)
                                            $color = 'gr';
                                        else if ($perc >= 40)
                                            $color = 'or';
                                        else
                                            $color = 'rd';

                                    }
                                @endphp
                                <td class="tb-row-small {{ $color }} {!! $prognose !!} {!! $error !!}">{{ $perc }}</td>
                                {{-- <td class="tb-row-small" title="{{$device->name}} - {{$device->location_name}} - {{$device->hive_name}} - {{ $date }}">{{ isset($d['devices'][$key]['perc']) ? $d['devices'][$key]['perc'] : '' }}</td> --}}
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