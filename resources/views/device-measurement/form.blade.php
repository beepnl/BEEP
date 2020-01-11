<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $devicemeasurement->name or ''}}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('inside') ? 'has-error' : ''}}">
	    <label for="inside" control-label>{{ 'Inside' }}</label>
	    <div>
	        <input class="form-control" name="inside" type="number" min="0" max="1" id="inside" value="{{ $devicemeasurement->inside or ''}}" >
	        {!! $errors->first('inside', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('zero_value') ? 'has-error' : ''}}">
	    <label for="zero_value" control-label>{{ 'Zero Value' }}</label>
	    <div>
	        <input class="form-control" name="zero_value" type="text" id="zero_value" value="{{ $devicemeasurement->zero_value or ''}}" >
	        {!! $errors->first('zero_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('unit_per_value') ? 'has-error' : ''}}">
	    <label for="unit_per_value" control-label>{{ 'Unit Per Value' }}</label>
	    <div>
	        <input class="form-control" name="unit_per_value" type="text" id="unit_per_value" value="{{ $devicemeasurement->unit_per_value or ''}}" >
	        {!! $errors->first('unit_per_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('measurement_id') ? 'has-error' : ''}}">
	    <label for="measurement_id" control-label>{{ 'Measurement' }}</label>
	    <div>
	        {!! Form::select('measurement_id', App\Measurement::selectList(), isset($devicemeasurement->measurement_id) ? $devicemeasurement->measurement_id : null, array('id'=>'measurement_id', 'class' => 'form-control select2', 'placeholder'=>'Select physical quantity...')) !!}
	        {!! $errors->first('measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('physical_quantity_id') ? 'has-error' : ''}}">
	    <label for="physical_quantity_id" control-label>{{ 'Physical quantity' }}</label>
	    <div>
	        {!! Form::select('physical_quantity_id', App\PhysicalQuantity::selectList(), isset($devicemeasurement->physical_quantity_id) ? $devicemeasurement->physical_quantity_id : null, array('id'=>'physical_quantity_id', 'class' => 'form-control select2', 'placeholder'=>'Select physical quantity...')) !!}
	        {!! $errors->first('physical_quantity_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('sensor_id') ? 'has-error' : ''}}">
	    <label for="sensor_id" control-label>{{ 'Sensor' }}*</label>
	    <div>
	        {!! Form::select('sensor_id', Auth::user()->sensors->sortBy('name')->pluck('name','id'), isset($devicemeasurement->sensor_id) ? $devicemeasurement->sensor_id : null, array('id'=>'sensor_id', 'class' => 'form-control select2', 'placeholder'=>'Select sensor...')) !!}
	        {!! $errors->first('sensor_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText or 'Create' }}">
    </div>
</div>
