<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $calculationmodel->name ?? ''}}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('data_measurement_id') ? 'has-error' : ''}}">
	    <label for="data_measurement_id" control-label>{{ 'IN: Data Measurement' }}</label>
	    <div>
	        {!! Form::select('data_measurement_id', App\Measurement::selectList(), e($calculationmodel->data_measurement_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.measurement')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('data_measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('measurement_id') ? 'has-error' : ''}}">
	    <label for="measurement_id" control-label>{{ 'OUT: Output Measurement' }}</label>
	    <div>
	        {!! Form::select('measurement_id', App\Measurement::selectList(), e($calculationmodel->measurement_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.measurement')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('data_interval') ? 'has-error' : ''}}">
	    <label for="data_interval" control-label>{{ 'Data Interval' }}</label>
	    <div>
	        <input class="form-control" name="data_interval" type="text" id="data_interval" value="{{ $calculationmodel->data_interval ?? ''}}" >
	        {!! $errors->first('data_interval', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('data_relative_interval') ? 'has-error' : ''}}">
	    <label for="data_relative_interval" control-label>{{ 'Relative Interval' }}</label>
	    <div>
	        <div class="radio">
		    <label><input name="data_relative_interval" type="radio" value="1" {{ (isset($calculationmodel) && 1 == $calculationmodel->data_relative_interval) ? 'checked' : '' }}> Yes</label>
		</div>
		<div class="radio">
		    <label><input name="data_relative_interval" type="radio" value="0" @if (isset($calculationmodel)) {{ (0 == $calculationmodel->data_relative_interval) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
		</div>
	        {!! $errors->first('data_relative_interval', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('data_interval_index') ? 'has-error' : ''}}">
	    <label for="data_interval_index" control-label>{{ 'Interval index (offset; positive = history, negative = future' }}</label>
	    <div>
	        <input class="form-control" name="data_interval_index" type="number" step="1" id="data_interval_index" value="{{ $calculationmodel->data_interval_index ?? ''}}" >
	        {!! $errors->first('data_interval_index', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('data_interval_amount') ? 'has-error' : ''}}">
	    <label for="data_interval_amount" control-label>{{ 'Interval amount' }}</label>
	    <div>
	        <input class="form-control" name="data_interval_amount" type="number" min="1" step="1" id="data_interval_amount" value="{{ $calculationmodel->data_interval_amount ?? ''}}" >
	        {!! $errors->first('data_interval_amount', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('calculation_interval_minutes') ? 'has-error' : ''}}">
	    <label for="calculation_interval_minutes" control-label>{{ 'Calculation interval minutes' }}</label>
	    <div>
	        <input class="form-control" name="calculation_interval_minutes" type="number" min="1" step="1" id="calculation_interval_minutes" value="{{ $calculationmodel->calculation_interval_minutes ?? ''}}" >
	        {!! $errors->first('calculation_interval_minutes', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('data_api_url') ? 'has-error' : ''}}">
	    <label for="data_api_url" control-label>{{ 'Data Api Url' }}</label>
	    <div>
	        <input class="form-control" name="data_api_url" type="text" id="data_api_url" value="{{ $calculationmodel->data_api_url ?? ''}}" >
	        {!! $errors->first('data_api_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('data_api_http_request') ? 'has-error' : ''}}">
	    <label for="data_api_http_request" control-label>{{ 'Data Api Http Request' }}</label>
	    <div>
	        <input class="form-control" name="data_api_http_request" type="text" id="data_api_http_request" value="{{ $calculationmodel->data_api_http_request ?? ''}}" >
	        {!! $errors->first('data_api_http_request', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('data_last_call') ? 'has-error' : ''}}">
	    <label for="data_last_call" control-label>{{ 'Data Last Call' }}</label>
	    <div>
	        <input class="form-control" name="data_last_call" type="datetime-local" id="data_last_call" value="{{ $calculationmodel->data_last_call ?? ''}}" >
	        {!! $errors->first('data_last_call', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('calculation') ? 'has-error' : ''}}">
	    <label for="calculation" control-label>{{ 'Calculation type' }}</label>
	    <div>
	        <div>
	        {!! Form::select('calculation', App\Models\CalculationModel::$calculations, e($calculationmodel->calculation ?? null), array('placeholder'=>__('crud.select', ['item'=>'Calculation type']),'class' => 'form-control select2')) !!}
	        {!! $errors->first('calculation', '<p class="help-block">:message</p>') !!}
	    </div>
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('repository_url') ? 'has-error' : ''}}">
	    <label for="repository_url" control-label>{{ 'Repository Url' }}</label>
	    <div>
	        <input class="form-control" name="repository_url" type="text" id="repository_url" value="{{ $calculationmodel->repository_url ?? ''}}" >
	        {!! $errors->first('repository_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
