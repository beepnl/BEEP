@extends('layouts.app')

@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' (ID: '.$research->id.')' }} Research dates: {{ substr($research->start_date, 0, 10) }} - {{ substr($research->end_date, 0, 10) }}
    @permission('role-edit')
        <a href="{{ route('research.edit', $research->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary pull-right"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
    @endpermission
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            Research consent data
        @endslot

        @slot('action')
        @endslot

        @slot('body')
            <div class="col-xs-12">
                <form method="GET" action="{{ route('research.show',$research->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
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
                            <button class="btn btn-primary btn-block loading-spinner" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i> Reload consent data table</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Data table -->
            <style type="text/css">
                .th.row-header {
                    max-height: 45px;
                    overflow: hidden;
                    white-space: nowrap;
                }

            </style>
            <div style="display: inline-block; width: 300px; margin-bottom: 15px;">
                <table class="table table-responsive table-striped table-header-rotated" style="overflow: scroll;">
                    <thead>
                        <tr>
                            <th class="rotate" style="min-width: 200px;">Database item</th>
                            <th class="rotate">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!isset($device_ids))
                        <tr>
                            <th class="row-header" style=""><span><i class="fa fa-2x fa-user"></i> Users</span></th> 
                            <th class="row-header">{{ $totals['users'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-map-marker"></i> Apiaries</span></th>
                            <th class="row-header">{{ $totals['apiaries'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-archive"></i> Hives</span></th> 
                            <th class="row-header">{{ $totals['hives'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-edit"></i> Inspections</span></th> 
                            <th class="row-header">{{ $totals['inspections'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-qrcode"></i> Sample codes</span></th>
                            <th class="row-header">{{ $totals['samplecodes'] }}</th>  
                        </tr>
                        @endif
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-feed"></i> Devices</span></th> 
                            <th class="row-header">{{ $totals['devices'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header" style="cursor: help;"  @if(isset($date_start)) title="Devices online after {{ substr($date_start, 0, 10) }}" @endif><span><i class="fa fa-2x fa-feed"></i> Devices online</span></th> 
                            <th class="row-header">{{ $totals['devices_online'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-list"></i> Flash logs</span></th> 
                            <th class="row-header">{{ $totals['flashlogs'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header" style="cursor: help;" title="Total amount of time stamps that have been wirelessly transferred via LoRa"><span><i class="fa fa-2x fa-line-chart"></i> Measurements</span></th> 
                            <th class="row-header">{{ $totals['measurements'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header" style="cursor: help;" title="Total amount of time stamps that have been imported by matching FlashLog data from the BEEP base memory to the database"><span><i class="fa fa-2x fa-line-chart"></i> Measurements imported</span></th> 
                            <th class="row-header">{{ $totals['measurements_imported'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header" style="cursor: help;" title="Sum of all time stamps in the database. Each time stamp contains multiple measurement values (e.g. weight, temperature, etc.). Only the timestamps are counted here. "><span><i class="fa fa-2x fa-line-chart"></i> Measurements total</span></th> 
                            <th class="row-header">{{ $totals['measurements_total'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header" style="cursor: help;" title="Total amount of 15 min time stamps divided by the amount of online devices. The data completeness of {{ $totals['data_completeness_online'] }}% represents the average of all daily data completeness calculations of the displayed dates."><span><i class="fa fa-2x fa-line-chart"></i><a href="/research/{{$research->id}}/data?{{request()->getQueryString()}}">Data completeness (%)</a></span></th> 
                            <th class="row-header">{{ $totals['data_completeness_online'] }}%</th> 
                        </tr>
                        <tr>
                            <th class="row-header" style="cursor: help;" title="Total amount of 15 min time stamps divided by the amount of devices. The data completeness of {{ $totals['data_completeness'] }}% represents the average of all daily data completeness calculations of the displayed dates."><span><i class="fa fa-2x fa-line-chart"></i> Data compl. all dev (%)</span></th> 
                            <th class="row-header">{{ $totals['data_completeness'] }}%</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-thermometer"></i> Weather data points</span></th> 
                            <th class="row-header">{{ $totals['weather'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa fa-exclamation-circle"></i> Active alert rules</span></th> 
                            <th class="row-header">{{ $totals['alert_rules'] }}</th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-bell"></i> Alerts</span></th> 
                            <th class="row-header">{{ $totals['alerts'] }}</th> 
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 310px); overflow-y: hidden; overflow-x: auto;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            @foreach($dates as $date => $d)
                                <th class="rotate"><div><span>{{ $date }}</span></div></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @if(!isset($device_ids))
                        <tr>
                            @foreach($dates as $date => $d)
                                <td style="cursor: help;" title="Users: {{ implode(', ', $d['user_names']) }}">{{ $d['users'] > 0 ? $d['users'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['apiaries'] > 0 ? $d['apiaries'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['hives'] > 0 ? $d['hives'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['inspections'] > 0 ? $d['inspections'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['samplecodes'] > 0 ? $d['samplecodes'] : '' }}</td>
                            @endforeach
                        </tr>
                        @endif
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['devices'] > 0 ? $d['devices'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td style="cursor: help;" title="Device gone offline: {{ implode(', ', $d['devices_offline']) }}&#10;&#10;Devices online: {{ implode(', ', $d['device_names']) }}">{{ $d['devices_online'] > 0 ? $d['devices_online'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['flashlogs'] > 0 ? $d['flashlogs'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['measurements'] > 0 ? $d['measurements'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['measurements_imported'] > 0 ? $d['measurements_imported'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['measurements_total'] > 0 ? $d['measurements_total'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['data_completeness_online'] > 0 ? $d['data_completeness_online'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['data_completeness'] > 0 ? $d['data_completeness'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['weather'] > 0 ? $d['weather'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['alert_rules'] > 0 ? $d['alert_rules'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['alerts'] > 0 ? $d['alerts'] : '' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Download button -->
            <br>
            <br>

            <div class="col-xs-12">
                @if(!isset($download_url))
                <form method="GET" action="{{ route('research.show',$research->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label control-label>Download dataset</label>
                            <span>(reload consent data table first)</span>
                            @foreach($consent_users_selected as $i => $id)
                                <input type="hidden" name="user_ids[{{ $i }}]" value="{{ $id }}">
                            @endforeach
                            @if(isset($date_start))
                                <input name="date_start" type="hidden" value="{{substr($date_start, 0, 10)}}" >
                            @endif
                            @if(isset($date_until))
                                <input name="date_until" type="hidden" value="{{substr($date_until, 0, 10)}}" >
                            @endif

                            <div class="row">
                                <div class="col-xs-6">
                                    <button name="download-meta" value="1" class="btn btn-success btn-block loading-spinner" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-download" aria-hidden="true"></i> Download selected Meta data Excel (faster)</button>
                                </div>
                                <div class="col-xs-6">
                                    <button name="download-all" value="1" class="btn btn-danger btn-block loading-spinner" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-download" aria-hidden="true"></i> Download all Meta + Measurements data Excel + CSVs (can take a few minutes)</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @else
                    <h3 control-label>Download dataset</h3>
                    <br>
                    <h4>Meta data (Excel)</h4>
                    <a href="{{$download_url}}" target="_blank"><i class="fa fa-download"></i> <i class="fa fa-file-excel-o"></i> Download selected meta data Excel file</a>
                    <div style="display:block; height: 10px;"></div>
                    @if(count($sensor_urls) > 0)
                        <h4>Measurements and Weather data (CSV)</h4>
                        <p>Exported CSV files are saved per device/location per consent period. 
                            All data per device in the highest possible resolution as comma separated (,) .csv file that you can open in Excel, or SPSS.
                            <br>
                            <em>NB: The date time data in the 'time' column is in GMT time (this differs from what you see in the BEEP app), formatted by the RFC 3339 date-time standard.</em>
                        </p>
                        @foreach($sensor_urls as $fileName => $url)
                            <a href="{{$url}}" target="_blank"><i class="fa fa-download"></i> {{ $fileName }}</a>
                            <br>
                        @endforeach
                    @endif

                @endif
            </div>

        @endslot
    @endcomponent
@endsection
