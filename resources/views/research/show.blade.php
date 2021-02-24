@extends('layouts.app')

@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' (ID: '.$research->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            Research consent data
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('research.edit', $research->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
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
                    <div class="col-md-1"></div>
                    <div class="col-xs-12 col-md-3">
                        <div class="form-group {{ $errors->has('date_start') ? 'has-error' : ''}}">
                            <label for="date_start" control-label>{{ 'From date (filters inspections/measurements/weather data)' }}</label>
                            <div>
                                <input class="form-control" name="date_start" type="date" id="date_start" min="{{substr($research->start_date, 0, 10)}}" max="{{substr($research->end_date, 0, 10)}}" value="{{ isset($date_start) ? substr($date_start, 0, 10) : '' }}" >
                                {!! $errors->first('date_start', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="col-xs-12 col-md-3">
                        <div class="form-group {{ $errors->has('date_until') ? 'has-error' : ''}}">
                            <label for="date_until" control-label>{{ 'Until date (filters inspections/measurements/weather data)' }}</label>
                            <div>
                                <input class="form-control" name="date_until" type="date" id="date_until" min="{{substr($research->start_date, 0, 10)}}" max="{{substr($research->end_date, 0, 10)}}" value="{{ isset($date_until) ? substr($date_until, 0, 10) : '' }}" >
                                {!! $errors->first('date_until', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <br>
                            <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-refresh" aria-hidden="true"></i> Reload consent data table</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Data table -->
            <div style="display: inline-block;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            <th class="rotate"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-user"></i> Users ({{ $totals['users'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-map-marker"></i> Apiaries ({{ $totals['apiaries'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-archive"></i> Hives ({{ $totals['hives'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-edit"></i> Inspections ({{ $totals['inspections'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-feed"></i> Devices ({{ $totals['devices'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-line-chart"></i> Measurements ({{ $totals['measurements'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-list"></i> Flash logs ({{ $totals['flashlogs'] }})</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-thermometer"></i> Weather data ({{ $totals['weather'] }})</span></th> 
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 240px); overflow-y: hidden; overflow-x: auto;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            @foreach($dates as $date => $d)
                                <th class="rotate"><div><span>{{ $date }}</span></div></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['users'] > 0 ? $d['users'] : '' }}</td>
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
                                <td>{{ $d['devices'] > 0 ? $d['devices'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['measurements'] > 0 ? $d['measurements'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['flashlogs'] > 0 ? $d['flashlogs'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['weather'] > 0 ? $d['weather'] : '' }}</td>
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
                            <input type="hidden" name="download" value="1">
                            <button class="btn btn-default btn-block loading-spinner" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-download" aria-hidden="true"></i> Download selected consent data set</button>
                        </div>
                    </div>
                </form>
                @else
                    <h3 control-label>Download dataset</h3>
                    <br>
                    <h4>Inspection data</h4>
                    <a href="{{$download_url}}" target="_blank"><i class="fa fa-download"></i> Download selected consent data set</a>
                    <div style="display:block; height: 10px;"></div>
                    @if(count($sensor_urls) > 0)
                        <h4>Sensor and weather data</h4>
                        <p>Export files are saved per device/location per consent period. 
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
