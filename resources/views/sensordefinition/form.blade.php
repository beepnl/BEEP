<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $sensordefinition->name }}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('inside') ? 'has-error' : ''}}">
	    <label for="inside" control-label>{{ 'Inside' }}</label>
	    <div>
	        <input class="form-control" name="inside" type="number" min="0" max="1" id="inside" value="{{ $sensordefinition->inside }}" >
	        {!! $errors->first('inside', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('offset') ? 'has-error' : ''}}">
	    <label for="offset" control-label>{{ 'Offset (zero Value)' }}</label>
	    <div>
	        <input class="form-control" name="offset" type="text" id="offset" value="{{ $sensordefinition->offset }}" >
	        {!! $errors->first('offset', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('multiplier') ? 'has-error' : ''}}">
	    <label for="multiplier" control-label>{{ 'Unit Per Value' }}</label>
	    <div>
	        <input class="form-control" name="multiplier" type="text" id="multiplier" value="{{ $sensordefinition->multiplier }}" >
	        {!! $errors->first('multiplier', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('input_measurement_id') ? 'has-error' : ''}}">
	    <label for="input_measurement_id" control-label>{{ 'Measurement' }}</label>
	    <div>
	        {!! Form::select('input_measurement_id', App\Measurement::selectList(), isset($sensordefinition->input_measurement_id) ? $sensordefinition->input_measurement_id : null, array('id'=>'input_measurement_id', 'class' => 'form-control select2', 'placeholder'=>'Select physical quantity...')) !!}
	        {!! $errors->first('input_measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('output_measurement_id') ? 'has-error' : ''}}">
	    <label for="output_measurement_id" control-label>{{ 'Measurement' }}</label>
	    <div>
	        {!! Form::select('output_measurement_id', App\Measurement::selectList(), isset($sensordefinition->output_measurement_id) ? $sensordefinition->output_measurement_id : null, array('id'=>'output_measurement_id', 'class' => 'form-control select2', 'placeholder'=>'Select physical quantity...')) !!}
	        {!! $errors->first('output_measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('device_id') ? 'has-error' : ''}}">
	    <label for="device_id" control-label>{{ 'Device' }}*</label>
	    <div>
	        {!! Form::select('device_id', App\Device::selectList(), isset($sensordefinition->device_id) ? $sensordefinition->device_id : null, array('id'=>'device_id', 'class' => 'form-control select2')) !!}
	        {!! $errors->first('device_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
