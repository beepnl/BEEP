@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' '.__('general.device') }}
@endsection

@section('content')

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			{{ __('crud.input_err') }}:<br>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif
	{!! Form::model($item, ['method' => 'PATCH','route' => ['devices.update', $item->id]]) !!}
	<div class="row">
		<div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>{{ __('crud.name') }}</label>
                {!! Form::text('name', null, array('placeholder' => __('crud.name'),'class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>{{ __('crud.type') }}</label>
                {!! Form::select('category_id', $types, $item->category_id, array('placeholder'=>__('crud.select', ['item'=>__('general.device').' '.__('general.type')]),'class' => 'form-control select2')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>LoRa DEV EUI:</label>
                {!! Form::text('key', null, array('placeholder' => __('crud.key'),'class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>Former DEV EUIs, after auto LoRa configure (comma separated):</label>
                {!! Form::text('former_key_list', null, array('placeholder' => 'Former keys','class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>{{ __('general.User') }}</label>
                {!! Form::select('user_id', App\User::selectlist(), $item->user_id, array('placeholder'=>__('crud.select', ['item'=>__('general.user')]),'class' => 'form-control select2')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>{{ __('beep.Hive') }}</label>
                {!! Form::select('hive_id', $hives, $item->hive_id, array('placeholder'=>__('crud.select', ['item'=>__('beep.Hive')]),'class' => 'form-control select2')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Hardware ID</label>
                {!! Form::text('hardware_id', null, array('placeholder' => 'Hardware ID','class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Battery voltage</label>
                <p>{{ $item->battery_voltage }}</p>
                {!! Form::hidden('battery_voltage', $item->battery_voltage) !!}
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Firmware version</label>
                <p>{{ $item->firmware_version }}</p>
                {!! Form::hidden('firmware_version', $item->firmware_version) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Hardware version</label>
                <p>{{ $item->hardware_version }}</p>
                {!! Form::hidden('hardware_version', $item->hardware_version) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Last message</label>
                <p>{{ $item->last_message_received }}</p>
                {!! Form::hidden('last_message_received', $item->last_message_received) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Boot count</label>
                <p>{{ $item->boot_count }}</p>
                {!! Form::hidden('boot_count', $item->boot_count) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Measurement interval min</label>
                <p>{{ $item->measurement_interval_min }}</p>
                {!! Form::hidden('measurement_interval_min', $item->measurement_interval_min) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Measurement transmission ratio</label>
                <p>{{ $item->measurement_transmission_ratio }}</p>
                {!! Form::hidden('measurement_transmission_ratio', $item->measurement_transmission_ratio) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>BLE PIN</label>
                <p>{{ $item->ble_pin }}</p>
                {!! Form::hidden('ble_pin', $item->ble_pin) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Next downlink mesaage (Not yet working)</label>
                {!! Form::text('next_downlink_message', null, array('placeholder' => 'HEX downlink message','class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Last downlink result</label>
                <p>{{ $item->last_downlink_result }}</p>
                {!! Form::hidden('last_downlink_result', $item->last_downlink_result) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Created date</label>
                <p>{{ $item->created_at }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <br>
			<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
	</div>
	{!! Form::close() !!}
@endsection