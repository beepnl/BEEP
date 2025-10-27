<div class="col-md-6 col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $sensordefinition->name }}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-md-6 col-xs-12">
	<div class="form-group {{ $errors->has('updated_at') ? 'has-error' : ''}}">
	    <label for="updated_at" control-label>Calibration start date (Updated at)</label>
	    <div>
	        <input class="form-control" name="updated_at" type="datetime-local" id="updated_at" value="{{ isset($sensordefinition->updated_at) ? substr(str_replace(' ', 'T', $sensordefinition->updated_at), 0, 16) : '' }}">
	        {!! $errors->first('updated_at', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('inside') ? 'has-error' : ''}}">
	    <label for="inside" control-label>{{ 'Inside' }}</label>
	    <div>
	        <input class="form-control" name="inside" type="number" min="0" max="1" id="inside" value="{{ $sensordefinition->inside }}" >
	        {!! $errors->first('inside', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('offset') ? 'has-error' : ''}}">
	    <label for="offset" control-label>{{ 'Offset (zero Value)' }}</label>
	    <div>
	        <input class="form-control" name="offset" type="text" id="offset" value="{{ $sensordefinition->offset }}" >
	        {!! $errors->first('offset', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('multiplier') ? 'has-error' : ''}}">
	    <label for="multiplier" control-label>{{ 'Unit Per Value' }}</label>
	    <div>
	        <input class="form-control" name="multiplier" type="text" id="multiplier" value="{{ $sensordefinition->multiplier }}" >
	        {!! $errors->first('multiplier', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('recalculate') ? 'has-error' : ''}}">
	    <label for="recalculate" control-label>Recalculate (always until next)</label>
	    <div>
	        <div class="radio">
    			<label><input name="recalculate" type="radio" value="1" @if (isset($sensordefinition)) {{ (1 == $sensordefinition->recalculate) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="recalculate" type="radio" value="0" {{ (isset($sensordefinition) && 0 == $sensordefinition->recalculate) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('recalculate', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="col-md-6 col-xs-12">
	<div class="form-group {{ $errors->has('input_measurement_id') ? 'has-error' : ''}}">
	    <label for="input_measurement_id" control-label>{{ 'Measurement' }}</label>
	    <div>
	        {!! Form::select('input_measurement_id', $measurement_select, isset($sensordefinition->input_measurement_id) ? $sensordefinition->input_measurement_id : null, array('id'=>'input_measurement_id', 'class' => 'form-control select2', 'placeholder'=>'Select physical quantity...')) !!}
	        {!! $errors->first('input_measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-md-6 col-xs-12">
	<div class="form-group {{ $errors->has('output_measurement_id') ? 'has-error' : ''}}">
	    <label for="output_measurement_id" control-label>{{ 'Measurement' }}</label>
	    <div>
	        {!! Form::select('output_measurement_id', $measurement_select, isset($sensordefinition->output_measurement_id) ? $sensordefinition->output_measurement_id : null, array('id'=>'output_measurement_id', 'class' => 'form-control select2', 'placeholder'=>'Select physical quantity...')) !!}
	        {!! $errors->first('output_measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('device_id') ? 'has-error' : ''}}">
	    <label for="device_id" control-label>{{ 'Device' }}*</label>
	    <div>
	        {!! Form::select('device_id', $devices_select, isset($sensordefinition->device_id) ? $sensordefinition->device_id : null, array('id'=>'device_id', 'class' => 'form-control select2')) !!}
	        {!! $errors->first('device_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
