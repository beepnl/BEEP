<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('abbreviation') ? 'has-error' : ''}}">
	    <label for="abbreviation" control-label>{{ 'Abbreviation' }}</label>
	    <div>
	        <input class="form-control" name="abbreviation" type="text" id="abbreviation" value="{{ isset($measurement->abbreviation) ? $measurement->abbreviation : '' }}" required>
	        {!! $errors->first('abbreviation', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('physical_quantity_id') ? 'has-error' : ''}}">
	    <label for="physical_quantity_id" control-label>{{ 'Physical Quantity Id' }}</label>
	    <div>
	        {!! Form::select('physical_quantity_id', App\PhysicalQuantity::selectList(), isset($measurement->physical_quantity_id) ? $measurement->physical_quantity_id : null, array('id'=>'physical_quantity_id', 'class' => 'form-control')) !!}
	        {!! $errors->first('physical_quantity_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<br>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('show_in_charts') ? 'has-error' : ''}}">
	    <label for="show_in_charts" control-label>{{ 'Show In Charts' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="show_in_charts" type="radio" value="1" @if (isset($measurement)) {{ (1 == $measurement->show_in_charts) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="show_in_charts" type="radio" value="0" {{ (isset($measurement) && 0 == $measurement->show_in_charts) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('show_in_charts', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('show_in_dials') ? 'has-error' : ''}}">
	    <label for="show_in_dials" control-label>{{ 'Show In Dials' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="show_in_dials" type="radio" value="1" @if (isset($measurement)) {{ (1 == $measurement->show_in_dials) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="show_in_dials" type="radio" value="0" {{ (isset($measurement) && 0 == $measurement->show_in_dials) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('show_in_dials', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('show_in_alerts') ? 'has-error' : ''}}">
	    <label for="show_in_alerts" control-label>{{ 'Show In Alerts' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="show_in_alerts" type="radio" value="1" @if (isset($measurement)) {{ (1 == $measurement->show_in_alerts) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="show_in_alerts" type="radio" value="0" {{ (isset($measurement) && 0 == $measurement->show_in_alerts) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('show_in_alerts', '<p class="help-block">:message</p>') !!}
    </div>
</div>
<div class="col-xs-12">
	<br>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('weather') ? 'has-error' : ''}}">
	    <label for="weather" control-label>{{ 'Weather related' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="weather" type="radio" value="1" @if (isset($measurement)) {{ (1 == $measurement->weather) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="weather" type="radio" value="0" {{ (isset($measurement) && 0 == $measurement->weather) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('weather', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('future') ? 'has-error' : ''}}">
	    <label for="future" control-label>{{ 'Get data from future' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="future" type="radio" value="1" @if (isset($measurement)) {{ (1 == $measurement->future) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="future" type="radio" value="0" {{ (isset($measurement) && 0 == $measurement->future) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('future', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="col-xs-12">
	<br>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('chart_group') ? 'has-error' : ''}}">
	    <label for="chart_group" control-label>{{ 'Chart Group' }}</label>
	    <div>
	        <input class="form-control" name="chart_group" type="number" id="chart_group" value="{{ isset($measurement->chart_group) ? $measurement->chart_group : '1' }}" >
	        {!! $errors->first('chart_group', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('min_value') ? 'has-error' : ''}}">
	    <label for="min_value" control-label>{{ 'Min value (sensor capability)' }}</label>
	    <div>
	        <input class="form-control" name="min_value" type="text" id="min_value" value="{{ isset($measurement->min_value) ? $measurement->min_value : '' }}" >
	        {!! $errors->first('min_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('max_value') ? 'has-error' : ''}}">
	    <label for="max_value" control-label>{{ 'Max value (sensor capability)' }}</label>
	    <div>
	        <input class="form-control" name="max_value" type="text" id="max_value" value="{{ isset($measurement->max_value) ? $measurement->max_value : '' }}" >
	        {!! $errors->first('max_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('hex_color') ? 'has-error' : ''}}">
	    <label for="hex_color" control-label>{{ 'Hexadecimal color code 6 digits (000000 == black)' }}</label>
	    <div>
	        <input class="form-control" name="hex_color" type="text" id="hex_color" maxlength="6" value="{{ isset($measurement->hex_color) ? $measurement->hex_color : '' }}" >
	        {!! $errors->first('hex_color', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('data_source_type') ? 'has-error' : ''}}">
	    <label for="data_source_type" control-label>{{ 'Data source type' }}</label>
	    <div>
	        <select name="data_source_type" class="form-control" id="data_source_type" required>
			    @foreach (App\Measurement::$data_source_types as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($measurement->data_source_type) && $measurement->data_source_type == $optionKey) ? 'selected' : ''}}>{{ $optionValue }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('data_source_type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('data_api_url') ? 'has-error' : ''}}">
	    <label for="data_api_url" control-label>{{ 'Data API URL (to get data from)' }}</label>
	    <div>
	        <input class="form-control" name="data_api_url" type="text" id="data_api_url" value="{{ isset($measurement->data_api_url) ? $measurement->data_api_url : '' }}" >
	        {!! $errors->first('data_api_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('data_repository_url') ? 'has-error' : ''}}">
	    <label for="data_repository_url" control-label>{{ 'Info url (Github/Helpdesk)' }}</label>
	    <div>
	        <input class="form-control" name="data_repository_url" type="text" id="data_repository_url" value="{{ isset($measurement->data_repository_url) ? $measurement->data_repository_url : '' }}" >
	        {!! $errors->first('data_repository_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ isset($submitButtonText) ? $submitButtonText : 'Create' }}">
    </div>
</div>
