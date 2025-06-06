<div class="col-xs-12">
	<div class="form-group {{ $errors->has('alert_rule_id') ? 'has-error' : ''}}">
	    <label for="alert_rule_id" control-label>{{ 'Alert Rule Id' }}</label>
	    <div>
	        {!! Form::select('alert_rule_id', App\Models\AlertRule::selectList(), e($alert->alert_rule_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.alert_rule')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('alert_rule_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('alert_function') ? 'has-error' : ''}}">
	    <label for="alert_function" control-label>{{ 'Alert Function' }}</label>
	    <div>
	        <input class="form-control" name="alert_function" type="text" id="alert_function" value="{{ $alert->alert_function ?? ''}}" >
	        {!! $errors->first('alert_function', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('alert_value') ? 'has-error' : ''}}">
	    <label for="alert_value" control-label>{{ 'Alert Value' }}</label>
	    <div>
	        <input class="form-control" name="alert_value" type="text" id="alert_value" value="{{ $alert->alert_value ?? ''}}" required>
	        {!! $errors->first('alert_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('measurement_id') ? 'has-error' : ''}}">
	    <label for="measurement_id" control-label>{{ 'Measurement Id' }}</label>
	    <div>
	        {!! Form::select('measurement_id', App\Measurement::selectList(), e($alert->measurement_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.measurement')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('show') ? 'has-error' : ''}}">
	    <label for="show" control-label>{{ 'Show' }}</label>
	    <div>
	        <div class="radio">
    <label><input name="show" type="radio" value="1" {{ (isset($alert) && 1 == $alert->show) ? 'checked' : '' }}> Yes</label>
</div>
<div class="radio">
    <label><input name="show" type="radio" value="0" @if (isset($alert)) {{ (0 == $alert->show) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
</div>
	        {!! $errors->first('show', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('location_name') ? 'has-error' : ''}}">
	    <label for="location_name" control-label>{{ 'Location Name' }}</label>
	    <div>
	        <input class="form-control" name="location_name" type="text" id="location_name" value="{{ $alert->location_name ?? ''}}" >
	        {!! $errors->first('location_name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_name') ? 'has-error' : ''}}">
	    <label for="hive_name" control-label>{{ 'Hive Name' }}</label>
	    <div>
	        <input class="form-control" name="hive_name" type="text" id="hive_name" value="{{ $alert->hive_name ?? ''}}" >
	        {!! $errors->first('hive_name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('device_name') ? 'has-error' : ''}}">
	    <label for="device_name" control-label>{{ 'Device Name' }}</label>
	    <div>
	        <input class="form-control" name="device_name" type="text" id="device_name" value="{{ $alert->device_name ?? ''}}" >
	        {!! $errors->first('device_name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('location_id') ? 'has-error' : ''}}">
	    <label for="location_id" control-label>{{ 'Location Id' }}</label>
	    <div>
	        {!! Form::select('location_id', App\Location::selectList(), e($alert->location_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.location')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('location_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_id') ? 'has-error' : ''}}">
	    <label for="hive_id" control-label>{{ 'Hive Id' }}</label>
	    <div>
	        <input class="form-control" name="hive_id" value="{{ isset($alert->hive_id) ? $alert->hive_id : '' }}" id="hive_id">
	        {!! $errors->first('hive_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('device_id') ? 'has-error' : ''}}">
	    <label for="device_id" control-label>{{ 'Device Id' }}</label>
	    <div>
	        {!! Form::select('device_id', App\Device::selectList(), e($alert->device_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.device')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('device_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User Id' }}</label>
	    <div>
	        {!! Form::select('user_id', App\User::selectList(), e($alert->user_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.user')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
