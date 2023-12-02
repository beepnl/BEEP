<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('active') ? 'has-error' : ''}}">
	    <label for="active" control-label>{{ 'Active' }}</label>
	    <div>
	        <div class="radio">
			    <label><input name="active" type="radio" value="1" {{ (isset($alertrule) && 1 == $alertrule->active) ? 'checked' : '' }}> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="active" type="radio" value="0" @if (isset($alertrule)) {{ (0 == $alertrule->active) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
			</div>
	        {!! $errors->first('active', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('default_rule') ? 'has-error' : ''}}">
	    <label for="default_rule" control-label>{{ 'Default Rule' }}</label>
	    <div>
	        <div class="radio">
		    <label><input name="default_rule" type="radio" value="1" {{ (isset($alertrule) && 1 == $alertrule->default_rule) ? 'checked' : '' }}> Yes</label>
		</div>
		<div class="radio">
		    <label><input name="default_rule" type="radio" value="0" @if (isset($alertrule)) {{ (0 == $alertrule->default_rule) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
		</div>
	        {!! $errors->first('default_rule', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('alert_via_email') ? 'has-error' : ''}}">
	    <label for="alert_via_email" control-label>{{ 'Alert Via Email' }}</label>
	    <div>
	        <div class="radio">
			    <label><input name="alert_via_email" type="radio" value="1" {{ (isset($alertrule) && 1 == $alertrule->alert_via_email) ? 'checked' : '' }}> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="alert_via_email" type="radio" value="0" @if (isset($alertrule)) {{ (0 == $alertrule->alert_via_email) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
			</div>
	        {!! $errors->first('alert_via_email', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('last_calculated_at') ? 'has-error' : ''}}">
	    <label for="last_calculated_at" control-label>{{ 'Last calculated at (YYYY-MM-DD HH:mm:ss)' }}</label>
	    <div>
	        <input class="form-control" name="last_calculated_at" type="text" id="last_calculated_at" value="{{ $alertrule->last_calculated_at ?? ''}}" >
	        {!! $errors->first('last_calculated_at', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('last_evaluated_at') ? 'has-error' : ''}}">
	    <label for="last_evaluated_at" control-label>{{ 'Last evaluated at (YYYY-MM-DD HH:mm:ss)' }}</label>
	    <div>
	        <input class="form-control" name="last_evaluated_at" type="text" id="last_evaluated_at" value="{{ $alertrule->last_evaluated_at ?? ''}}" >
	        {!! $errors->first('last_evaluated_at', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $alertrule->name ?? ''}}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('description') ? 'has-error' : ''}}">
	    <label for="description" control-label>{{ 'Description' }}</label>
	    <div>
	        <input class="form-control" name="description" type="text" id="description" value="{{ $alertrule->description ?? ''}}" >
	        {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('measurement_id') ? 'has-error' : ''}}">
	    <label for="measurement_id" control-label>{{ 'Measurement Id' }}</label>
	    <div>
	        {!! Form::select('measurement_id', App\Measurement::selectList(), e($alertrule->measurement_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.measurement')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('calculation') ? 'has-error' : ''}}">
	    <label for="calculation" control-label>{{ 'Calculation' }}</label>
	    <div>
	        <select name="calculation" class="form-control" id="calculation" required>
			    @foreach (App\Models\AlertRule::$calculations as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertrule->calculation) && $alertrule->calculation == $optionKey) ? 'selected' : ''}}>{{ $optionValue }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('calculation', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('calculation_minutes') ? 'has-error' : ''}}">
	    <label for="calculation_minutes" control-label>{{ 'Calculation Minutes' }}</label>
	    <div>
	        <input class="form-control" name="calculation_minutes" type="number" id="calculation_minutes" value="{{ $alertrule->calculation_minutes ?? '60'}}" >
	        {!! $errors->first('calculation_minutes', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('comparison') ? 'has-error' : ''}}">
	    <label for="comparison" control-label>{{ 'Comparison' }}</label>
	    <div>
	        <select name="comparison" class="form-control" id="comparison" required>
			    @foreach (App\Models\AlertRule::$comparisons as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertrule->comparison) && $alertrule->comparison == $optionKey) ? 'selected' : ''}}>{{ $optionValue }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('comparison', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('comparator') ? 'has-error' : ''}}">
	    <label for="comparator" control-label>{{ 'Comparator' }}</label>
	    <div>
	        <select name="comparator" class="form-control" id="comparator" required>
			    @foreach (App\Models\AlertRule::$comparators as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertrule->comparator) && $alertrule->comparator == $optionKey) ? 'selected' : ''}}>{{ $optionKey }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('comparator', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('threshold_value') ? 'has-error' : ''}}">
	    <label for="threshold_value" control-label>{{ 'Threshold Value' }}</label>
	    <div>
	        <input class="form-control" name="threshold_value" type="text" id="threshold_value" value="{{ $alertrule->threshold_value ?? ''}}" required>
	        {!! $errors->first('threshold_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('alert_on_occurrences') ? 'has-error' : ''}}">
	    <label for="alert_on_occurrences" control-label>{{ 'Alert on occurences' }}</label>
	    <div>
	        <input class="form-control" name="alert_on_occurrences" type="number" id="alert_on_occurrences" value="{{ $alertrule->alert_on_occurrences ?? '1'}}">
	        {!! $errors->first('alert_on_occurrences', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('exclude_months') ? 'has-error' : ''}}">
	    <label for="exclude_months" control-label>{{ 'Exclude Months' }}</label>
	    <div>
	        {!! Form::select('exclude_months[]', App\Models\AlertRule::$exclude_months, isset($alertrule->exclude_months) ? $alertrule->getExcludeMonthsAttribute() : null, array('class' => 'form-control select2', 'multiple')) !!}
	        {!! $errors->first('exclude_months', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('exclude_hours') ? 'has-error' : ''}}">
	    <label for="exclude_hours" control-label>{{ 'Exclude Hours' }}</label>
	    <div>
	        {!! Form::select('exclude_hours[]', App\Models\AlertRule::$exclude_hours, isset($alertrule->exclude_hours) ? $alertrule->getExcludeHoursAttribute() : null, array('class' => 'form-control select2', 'multiple')) !!}
	        {!! $errors->first('exclude_hours', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('exclude_hive_ids') ? 'has-error' : ''}}">
	    <label for="exclude_hive_ids" control-label>{{ 'Exclude Hives' }}</label>
	    <div>
	        {!! Form::select('exclude_hive_ids[]', App\Hive::selectList(true), isset($alertrule->exclude_hive_ids) ? $alertrule->getExcludeHiveIdsAttribute() : null, array('class' => 'form-control select2', 'multiple')) !!}
	        {!! $errors->first('exclude_hive_ids', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('webhook_url') ? 'has-error' : ''}}">
	    <label for="webhook_url" control-label>{{ 'Webhook Url' }}</label>
	    <div>
	        <input class="form-control" name="webhook_url" type="text" id="webhook_url" value="{{ $alertrule->webhook_url ?? ''}}" >
	        {!! $errors->first('webhook_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User Id' }}</label>
	    <div>
	        {!! Form::select('user_id', App\User::selectList(), e($alertrule->user_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.user')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
