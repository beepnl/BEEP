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
                <div class="col-xs-12 col-md-4">
                    <div class="form-group {{ $errors->has('user_ids') ? 'has-error' : ''}}">
                        <label for="user_ids" control-label>{{ 'Select consented users' }} ({{ count($consent_users_selected) }} / {{ count($consent_users_select) }})</label>
                        <div>
                            {!! Form::select('user_ids[]', $consent_users_select, $consent_users_selected, array('id'=>'user_ids','class' => 'form-control select2', 'multiple')) !!}
                            {!! $errors->first('user_ids', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group {{ $errors->has('device_ids') ? 'has-error' : ''}}">
                        <label for="device_ids" control-label>{{ 'Devices filter (default: all)' }}</label>
                        <div>
                            {!! Form::select('device_ids[]', $devices_select, $device_ids, array('id'=>'device_ids','class' => 'form-control select2', 'multiple')) !!}
                            {!! $errors->first('device_ids', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>
              
                <div class="col-xs-12 col-md-2">
                    <div class="form-group {{ $errors->has('date_start') ? 'has-error' : ''}}">
                        <label for="date_start" control-label>{{ 'From date (filters time based data)' }}</label>
                        <div>
                            <input class="form-control" name="date_start" type="date" id="date_start" min="{{substr($research->start_date, 0, 10)}}" max="{{substr($research->end_date, 0, 10)}}" value="{{ isset($date_start) ? substr($date_start, 0, 10) : '' }}" >
                            {!! $errors->first('date_start', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-2">
                    <div class="form-group {{ $errors->has('date_until') ? 'has-error' : ''}}">
                        <label for="date_until" control-label>{{ 'Until date (filters time based data)' }}</label>
                        <div>
                            <input class="form-control" name="date_until" type="date" id="date_until" min="{{substr($research->start_date, 0, 10)}}" max="{{substr($research->end_date, 0, 10)}}" value="{{ isset($date_until) ? substr($date_until, 0, 10) : '' }}" >
                            {!! $errors->first('date_until', '<p class="help-block">:message</p>') !!}
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
            <!-- Data table -->

            <div style="display: inline-block; width: 500px; overflow-y: hidden; overflow-x: scroll;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            <th class="rotate">Device</th>
                            <th class="rotate">Data</th>
                            <th class="rotate">Apiary</th>
                            <th class="rotate">Hive</th>
                            @if($add_flashlogs)
                            <th class="rotate">Flashlogs</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices_show as $device)
                        <tr class="tb-row-small" @if (isset($device->deleted_at)) style="color: #AAA;" title="Device has been deleted at {{$device->deleted_at}}" @else title="{{ $device->name }}" @endif>
                            <th title="{{ $device->name }}" class="tb-row-very-small row-header">{{ $device->name }}</th> 
                            <th class="tb-row-very-small row-header">{{ isset($totals['devices'][$device->key]) ? $totals['devices'][$device->key]['data_completeness'].'%' : '' }}</th> 
                            <th title="{{ $device->location_name }}" class="tb-row-very-small row-header">{{ $device->location_name }}</th> 
                            <th title="{{ $device->hive_name }}" class="tb-row-very-small row-header">{{ $device->hive_name }}</th> 
                            @if($add_flashlogs)
                            <th class="tb-row-small row-header" style="padding-top: 0; padding-bottom: 0">{!! $device->getFlashLogsHtml($date_start) !!}</th>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 510px); overflow-y: hidden; overflow-x: scroll;">
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

        @endslot
    @endcomponent

@endsection