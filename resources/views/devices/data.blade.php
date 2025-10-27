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
            <div class="input-group" title="Overrules the device interval (to correct if it changed over the years)" style="display: inline-block; width: 200px;">
                <select class="form-control" style="max-width: 160px;" name="interval_min" value="{{ request('interval_min') }}">
                    <option value="" @if(request('interval_min') == '') selected @endif>Use device interval</option>
                    <option value="1" @if(request('interval_min') == 1) selected @endif>1 min</option>
                    <option value="5" @if(request('interval_min') == 5) selected @endif>5 min</option>
                    <option value="10" @if(request('interval_min') == 10) selected @endif>10 min</option>
                    <option value="15" @if(request('interval_min') == 15) selected @endif>15 min</option>
                    <option value="30" @if(request('interval_min') == 30) selected @endif>30 min</option>
                    <option value="60" @if(request('interval_min') == 60) selected @endif>1 hour</option>
                </select>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-clock-o"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 100px;" name="research" placeholder="Research..." value="{{ request('research') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 100px;" name="user" placeholder="User..." value="{{ request('user') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 100px;" name="search" placeholder="Device properties..." value="{{ request('search') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            {!! Form::close() !!}
            <span><h5><em>NB: Fill at least one of the 'Research', 'User' & 'Device properties' filter properties to show Data and Completeness. The filter shows 10 items per page.</em></h5></span>
        @endslot

        @slot('$bodyClass')
        @endslot

        @slot('body')

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

            <table id="table-devices" class="table table-responsive table-striped">
                <thead>
                    <tr>
                        <th>{{ __('crud.id') }}</th>
                        {{-- <th>Sticker</th> --}}
                        <th>{{ __('crud.name') }}</th>
                        <th>Data start date</th>
                        <th>Data end date</th>
                        <th>{{ __('crud.type') }}</th>
                        <th style="min-width: 140px;">DEV EUI ({{ __('crud.key') }}) / HW ID</th>
                        <th>Data points</th>
                        <th>Data imported</th>
                        <th>Interval (min) * ratio</th>
                        <th>Completeness</th>
                        <th>{{ __('general.User') }} / {{ __('beep.Hive') }}</th>
                        <th>Research</th>
                        <th style="min-width: 50px;">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($devices as $key => $device)
                    <tr @if (isset($device->deleted_at)) style="color: #AAA;" @endif>
                        <td>{{ $device->id }}</td>
                        {{-- <td><button onclick="copyTextToClipboard('{{ $device->name }}\r\n{{ $device->hardware_id }}');">Copy</button></td> --}}
                        <td>{{ $device->name }}</td>
                        <td>{{ $device->date_data_start }}</td>
                        <td>{{ $device->date_data_end }}</td>
                        <td><label class="label label-default">{{ $device->type }}</label></td>
                        <td>{{ $device->key }} <span style="font-size: 10px">{{ isset($device->former_key_list) ? '(former: '.str_replace(',', ', ', $device->former_key_list).')' : ''}}</span> / {{ $device->hardware_id }}</td>
                        <td>{{ $device->data_points }}<br>({{ round($device->data_interval_min * $device->data_points / 1440) }} d)</td>
                        <td>{{ $device->data_imported }}<br>({{ round($device->data_interval_min * $device->data_imported / 1440) }} d)</td>
                        <td>{{ $device->data_interval_min }} * {{ $device->measurement_transmission_ratio }}</td>
                        <td><strong>{{ $device->completeness }} %</strong><br>({{ $device->data_days }} / {{ $device->total_days }} d)</td>
                        <td>{{ $device->user->name }} / {{ isset($device->hive) ? $device->hive->name : '' }}</td>
                        <td><p style="font-size: 10px">{{ $device->researchNames() }}</p></td>
                        <td>
                            @if (isset($device->deleted_at)) <p>Deleted: {{$device->deleted_at}}</p> @else <a class="btn btn-default" href="{{ route('devices.show',$device->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a> @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper"> {!! $devices->appends(request()->except('page'))->render() !!} </div>
        @endslot
    @endcomponent

    @component('components/box')
        @slot('title')
            CSV Data
        @endslot

        @slot('$bodyClass')
        @endslot

        @slot('body')

<textarea style="width: 100%;" rows="15">
User name, Hive name, Device ID, Device name, Data start date, Data end date, Data size (timestamps), Data length (days), Data size imported (timestamps), Data length imported (days), Data interval (min), LoRa transmission ratio (x interval), Data completeness (%), Researches
@foreach ($devices as $key => $device)
"{{ $device->user->name }}","{{ isset($device->hive) ? $device->hive->name : '' }}",{{ $device->id }},"{{ $device->name }}",{{ $device->date_data_start }},{{ $device->date_data_end }},{{ $device->data_points }},{{ round($device->data_interval_min * $device->data_points / 1440, 1) }},{{ $device->data_imported }},{{ round($device->data_interval_min * $device->data_imported / 1440, 1) }},{{ $device->data_interval_min }},{{ $device->measurement_transmission_ratio }},{{ $device->completeness }},"{{ $device->researchNames() }}"
@endforeach
</textarea>

        @endslot
    @endcomponent

@endsection