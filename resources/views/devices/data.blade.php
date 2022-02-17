@extends('layouts.app')
 
@section('page-title') Data overview {{ __('general.devices') }}
@endsection

@section('content')

            
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('general.devices')]) }}
            <br>
            {!! Form::open(['method' => 'GET', 'route' => 'devices.data', 'class' => 'form-inline', 'role' => 'search'])  !!}
            <div class="input-group" style="display: inline-block; width: 200px;">
                <select class="form-control" style="max-width: 160px;" name="year" value="{{ request('year') }}">
                    @for($year=date('Y'); $year > 2018; $year--)
                        <option value="{{ $year }}" @if($year == request('year')) selected @endif>{{ $year }}</option>
                    @endfor
                </select>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-calendar"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 160px;" name="research" placeholder="Research..." value="{{ request('research') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 160px;" name="user" placeholder="User..." value="{{ request('user') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 160px;" name="search" placeholder="Device properties..." value="{{ request('search') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            {!! Form::close() !!}
            <span><h5><em>NB: De 'Research', 'User' & 'Device properties' filter velden filteren max 10 devices op volgorde van het laatste contact uit de database</em></h5></span>
        @endslot

        @slot('$bodyClass')
        @endslot

        @slot('body')


        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-devices").DataTable(
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

            <table id="table-devices" class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ __('crud.id') }}</th>
                        {{-- <th>Sticker</th> --}}
                        <th>{{ __('crud.name') }}</th>
                        <th>{{ __('crud.created_at') }}</th>
                        <th>{{ __('crud.type') }}</th>
                        <th style="min-width: 140px;">DEV EUI ({{ __('crud.key') }}) / HW ID</th>
                        <th>Data points</th>
                        <th>Data imported</th>
                        <th>Interval (min) / ratio</th>
                        <th>Completeness</th>
                        <th>{{ __('general.User') }} / {{ __('beep.Hive') }}</th>
                        <th>Research</th>
                        <th style="min-width: 50px;">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $key => $device)
                    <tr>
                        <td>{{ $device->id }}</td>
                        {{-- <td><button onclick="copyTextToClipboard('{{ $device->name }}\r\n{{ $device->hardware_id }}');">Copy</button></td> --}}
                        <td>{{ $device->name }}</td>
                        <td>{{ $device->created_at }}</td>
                        <td><label class="label label-default">{{ $device->type }}</label></td>
                        <td>{{ $device->key }} <span style="font-size: 10px">{{ isset($device->former_key_list) ? '(former: '.str_replace(',', ', ', $device->former_key_list).')' : ''}}</span> / {{ $device->hardware_id }}</td>
                        <td>{{ $device->data_points }}<br>({{ round($device->measurement_interval_min * $device->data_points / 1440) }} d)</td>
                        <td>{{ $device->data_imported }}<br>({{ round($device->measurement_interval_min * $device->data_imported / 1440) }} d)</td>
                        <td>{{ $device->measurement_interval_min }} / {{ $device->measurement_transmission_ratio }} @if(isset($device->measurement_interval_min)) (=send 1x/{{ $device->measurement_interval_min * max(1,$device->measurement_transmission_ratio) }}min) @endif</td>
                        <td>{{ $device->completeness }} %</td>
                        <td>{{ $device->user->name }} / {{ isset($device->hive) ? $device->hive->name : '' }}</td>
                        <td><p style="font-size: 10px">{{ $device->researchNames() }}</p></td>
                        <td style="max-width: 200px; max-height: 60px; overflow: hidden;" title="{{ $device->last_downlink_result }}">{{ $device->last_downlink_result }}</td>
                        <td>
                            <a class="btn btn-default" href="{{ route('devices.show',$device->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endslot
    @endcomponent
@endsection