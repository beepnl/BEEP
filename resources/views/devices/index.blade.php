@extends('layouts.app')
 
@section('page-title') {{ __('crud.management', ['item'=>__('general.device')]) }}
@endsection

@section('content')
@role('superadmin')
<meta name="api-token" content="{{ Auth::user()->api_token }}">
@endrole

			
	@component('components/box')
		@slot('title')
			{{ __('crud.overview', ['item'=>__('general.devices')]) }}
	        {!! Form::open(['method' => 'GET', 'route' => 'devices.index', 'class' => 'form-inline', 'role' => 'search'])  !!}
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
	        <span><h5><em>NB: De 'Research', 'User' & 'Device properties' filter velden filteren max 50 devices op volgorde van het laatste contact uit de database</em></h5></span>
		@endslot

		@slot('action')
			@permission('sensor-create')
	            <a class="btn btn-primary" href="{{ route('devices.create') }}"><i class="fa fa-plus"></i> {{ __('crud.add_a', ['item'=>__('general.device')]) }}</a>
	        @endpermission
		@endslot

		@slot('$bodyClass')
		@endslot

		@slot('body')

		<script type="text/javascript">
            $(document).ready(function() {
                $("#table-sensors").DataTable(
                {
                    "pageLength": 50,
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 4, "desc" ]
                    ],
                });
            });
        </script>

        <script type="text/javascript">
            $(document).on('click', '.device-sync-btn', function(e) {
                e.preventDefault();
                var button = $(this);
                var deviceKey = button.data('device-key');
                
                // Check if API token is available (user is superadmin)
                var apiTokenMeta = $('meta[name="api-token"]');
                if (apiTokenMeta.length === 0) {
                    alert('This feature requires superadmin privileges.');
                    return;
                }
                
                var apiToken = apiTokenMeta.attr('content');
                
                if (!confirm('This will sync the device clock and then reset the device. Continue?')) {
                    return;
                }
                
                button.prop('disabled', true);
                button.html('<i class="fa fa-spinner fa-spin"></i>');
                
                // First call clocksync
                $.ajax({
                    url: '/api/devices/clocksync',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + apiToken,
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: JSON.stringify({key: deviceKey}),
                    success: function(response) {
                        console.log('Clock sync successful:', response);
                        
                        // Then call lora_reset
                        $.ajax({
                            url: '/api/devices/lora_reset',
                            type: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + apiToken,
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: JSON.stringify({key: deviceKey}),
                            success: function(response) {
                                console.log('LoRa reset successful:', response);
                                alert('Device sync and reset commands sent successfully!');
                                button.prop('disabled', false);
                                button.html('<i class="fa fa-refresh"></i>');
                            },
                            error: function(xhr, status, error) {
                                console.error('LoRa reset failed:', error);
                                alert('Failed to send reset command: ' + (xhr.responseJSON?.error || error));
                                button.prop('disabled', false);
                                button.html('<i class="fa fa-refresh"></i>');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Clock sync failed:', error);
                        alert('Failed to sync clock: ' + (xhr.responseJSON?.error || error));
                        button.prop('disabled', false);
                        button.html('<i class="fa fa-refresh"></i>');
                    }
                });
            });
        </script>

			<table id="table-sensors" class="table table-striped">
				<thead>
					<tr>
						<th>{{ __('crud.id') }}</th>
						{{-- <th>Sticker</th> --}}
						<th>{{ __('crud.name') }}</th>
						<th>{{ __('crud.type') }}</th>
						<th style="min-width: 170px;">DEV EUI ({{ __('crud.key') }}) / HW ID</th>
						<th style="min-width: 140px;">Last seen (UTC)</th>
						<th><img src="/img/icn_bat.svg" style="width: 20px;"></th>
						<th>RTC</th>
						<th>Hardware version</th>
						<th style="min-width: 100px;">Firmware version</th>
						<th>Interval (min) / ratio</th>
						<th>{{ __('general.User') }} / {{ __('beep.Hive') }}</th>
						<th>Research</th>
						<th>Last downlink result</th>
						<th style="min-width: 120px;">{{ __('crud.actions') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($sensors as $key => $device)
					<tr @if (isset($device->deleted_at)) style="color: #AAA;" @endif>
						<td>{{ $device->id }}</td>
						{{-- <td><button onclick="copyTextToClipboard('{{ $device->name }}\r\n{{ $device->hardware_id }}');">Copy</button></td> --}}
						<td>{{ $device->name }}</td>
						<td><label class="label label-default">{{ $device->type }}</label></td>
						<td>{{ $device->key }} <span style="font-size: 10px">{{ isset($device->former_key_list) ? '(former: '.str_replace(',', ', ', $device->former_key_list).')' : ''}}</span> / {{ $device->hardware_id }}</td>
						<td>{{ $device->last_message_received }}</td>
						<td>{{ isset($device->battery_voltage) ? $device->battery_voltage.' V' : '' }}</td>
						<td>{{ $device->rtc }}</td>
						<td><p style="font-size: 10px">{{ $device->hardware_version }}</p></td>
						<td><p style="font-size: 10px">{{ $device->firmware_version }} @if(isset($device->datetime)) ({{ $device->datetime_offset_sec > 0 ? '+'.$device->datetime_offset_sec : $device->datetime_offset_sec }} sec)<br>{{ $device->datetime }}@endif</p></td>
						<td>{{ $device->measurement_interval_min }} / {{ $device->measurement_transmission_ratio }} @if(isset($device->measurement_interval_min)) (=send 1x/{{ $device->measurement_interval_min * max(1,$device->measurement_transmission_ratio) }}min) @endif</td>
						<td>{{ $device->user->name }} / {{ isset($device->hive) ? $device->hive->name : '' }}</td>
						<td><p style="font-size: 10px">{{ $device->researchNames() }}</p></td>
						<td style="max-width: 200px; max-height: 60px; overflow: hidden;" title="{{ $device->last_downlink_result }}">{{ $device->last_downlink_result }}</td>
						<td>
							@if (isset($device->deleted_at)) 
								<a class="btn btn-danger pull-right" title="Undelete this devive deleted at: {{$device->deleted_at}}" href="{{ route('devices.undelete',$device->id) }}"><i class="fa fa-refresh"></i></a>
							@else 
								
								<a class="btn btn-default" href="{{ route('devices.show',$device->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a>
								@permission('sensor-edit')
								<a class="btn btn-primary" href="{{ route('devices.edit',$device->id) }}" title="{{ __('crud.edit') }}"><i class="fa fa-pencil"></i></a>
								@endpermission
								@permission('sensor-edit')
								<button class="btn btn-warning device-sync-btn" data-device-key="{{ $device->key }}" title="Sync device clock and reset"><i class="fa fa-refresh"></i></button>
								@endpermission
								@permission('sensor-delete')
								{!! Form::open(['method' => 'DELETE','route' => ['devices.destroy', $device->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("'.__('crud.sure',['item'=>__('general.sensor'),'name'=>'\''.$device->name.'\'']).'")']) !!}
					            {!! Form::button('<i class="fa fa-trash-o"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right']) !!}
					        	{!! Form::close() !!}
					        	@endpermission
					        @endif
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>

			<div class="pagination-wrapper"> {!! $sensors->appends(request()->except('page'))->render() !!} </div>
		@endslot
	@endcomponent
@endsection