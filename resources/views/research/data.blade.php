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
                        <button class="btn btn-primary btn-block loading-spinner" type="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i> Reload data completeness table</button>
                    </div>
                </div>
            </form>

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
                    overflow: scroll; 
                    white-space: nowrap;" 
                }
            </style>

            <!-- Data table -->
            <div style="display: inline-block; width: 400px; overflow: scroll;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            <th class="rotate">Device</th>
                            <th class="rotate">Apiary</th>
                            <th class="rotate">Hive</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user_devices as $key => $device)
                        <tr class="tb-row-small" @if (isset($device->deleted_at)) style="color: #AAA;" title="Device has been deleted at {{$device->deleted_at}}" @endif>
                            <th class="tb-row-small row-header">{{ $device->name }}</th> 
                            <th class="tb-row-small row-header">{{ $device->location_name }}</th> 
                            <th class="tb-row-small row-header">{{ $device->hive_name }}</th> 
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 410px); overflow-y: hidden; overflow-x: auto;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            @foreach($dates as $date => $d)
                                <th class="rotate"><div><span>{{ $date }}</span></div></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user_devices as $key => $device)
                        <tr>
                            @foreach($dates as $date => $d)
                                <td class="tb-row-small">{{ isset($d['devices'][$key]['perc']) ? $d['devices'][$key]['perc'].'%' : '' }}</td>
                            @endforeach
                        </tr>
                       @endforeach
                    </tbody>
                </table>
            </div>

        @endslot
    @endcomponent

@endsection