@extends('layouts.app')
 
@section('page-title') {{ __('crud.management', ['item'=>__('general.device')]) }}
@endsection

@section('content')

			
	@component('components/box')
		@slot('title')
			{{ __('crud.overview', ['item'=>__('general.devices')]) }}
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
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 0, "desc" ]
                    ],
                });
            });

            function fallbackCopyTextToClipboard(text) {
			  var textArea = document.createElement("textarea");
			  textArea.value = text;
			  textArea.style.position="fixed";  //avoid scrolling to bottom
			  document.body.appendChild(textArea);
			  textArea.focus();
			  textArea.select();

			  try {
			    var successful = document.execCommand('copy');
			    var msg = successful ? 'successful' : 'unsuccessful';
			    console.log('Fallback: Copying text command was ' + msg);
			  } catch (err) {
			    console.error('Fallback: Oops, unable to copy', err);
			  }

			  document.body.removeChild(textArea);
			}
			function copyTextToClipboard(text) {
			  if (!navigator.clipboard) {
			    fallbackCopyTextToClipboard(text);
			    return;
			  }
			  navigator.clipboard.writeText(text).then(function() {
			    console.log('Async: Copying to clipboard was successful!');
			  }, function(err) {
			    console.error('Async: Could not copy text: ', err);
			  });
			}
        </script>

			<table id="table-sensors" class="table table-striped">
				<thead>
					<tr>
						<th>{{ __('crud.id') }}</th>
						<th>Sticker</th>
						<th>{{ __('crud.name') }}</th>
						<th>{{ __('crud.type') }}</th>
						<th>{{ __('crud.key') }}</th>
						<th>Last seen</th>
						<th>Hardware ID</th>
						<th>Hardware version</th>
						<th>Firmware version</th>
						<th>Inerval (min) / ratio</th>
						<th>{{ __('general.User') }}</th>
						<th>{{ __('beep.Hive') }}</th>
						<th>{{ __('crud.actions') }}</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($sensors as $key => $sensor)
					<tr>
						<td>{{ $sensor->id }}</td>
						<td><button onclick="copyTextToClipboard('{{ $sensor->name }}\r\n{{ $sensor->hardware_id }}');">Copy</button></td>
						<td>{{ $sensor->name }}</td>
						<td><label class="label label-default">{{ $sensor->type }}</label></td>
						<td>{{ $sensor->key }}</td>
						<td>{{ $sensor->last_message_received }}</td>
						<td>{{ $sensor->hardware_id }}</td>
						<td>{{ $sensor->hardware_version }}</td>
						<td>{{ $sensor->firmware_version }}</td>
						<td>{{ $sensor->transmission_interval_min }} / {{$sensor->measurement_transmission_ratio}}</td>
						<td>{{ $sensor->user->name }}</td>
						<td>{{ isset($sensor->hive) ? $sensor->hive->name : '' }}</td>
						<td>
							<a class="btn btn-default" href="{{ route('devices.show',$sensor->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a>
							@permission('sensor-edit')
							<a class="btn btn-primary" href="{{ route('devices.edit',$sensor->id) }}" title="{{ __('crud.edit') }}"><i class="fa fa-pencil"></i></a>
							@endpermission
							@permission('sensor-delete')
							{!! Form::open(['method' => 'DELETE','route' => ['devices.destroy', $sensor->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("'.__('crud.sure',['item'=>__('general.sensor'),'name'=>'\''.$sensor->name.'\'']).'")']) !!}
				            {!! Form::button('<i class="fa fa-trash-o"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right']) !!}
				        	{!! Form::close() !!}
				        	@endpermission
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		@endslot
	@endcomponent
@endsection