<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('alert_rule_id') ? 'has-error' : ''}}">
	    <label for="alert_rule_id" control-label>{{ 'AlertRuleFormula' }}</label>
	    <div>
	        {!! Form::select('alert_rule_id', App\Models\AlertRule::selectList(), e($alertruleformula->alert_rule_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('beep.AlertRuleFormula')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('alert_rule_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-6">
	<div class="form-group {{ $errors->has('measurement_id') ? 'has-error' : ''}}">
	    <label for="measurement_id" control-label>{{ 'Measurement' }}</label>
	    <div>
	        {!! Form::select('measurement_id', App\Measurement::selectList(), e($alertruleformula->measurement_id ?? null), array('placeholder'=>__('crud.select', ['item'=>__('general.measurement')]),'class' => 'form-control select2')) !!}
	        {!! $errors->first('measurement_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('calculation') ? 'has-error' : ''}}">
	    <label for="calculation" control-label>{{ 'Calculation' }}</label>
	    <div>
	        <select name="calculation" class="form-control" id="calculation" required>
			    @foreach (App\Models\AlertRuleFormula::$calculations as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertruleformula->calculation) && $alertruleformula->calculation == $optionKey) ? 'selected' : ''}}>{{ $optionValue }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('calculation', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('comparison') ? 'has-error' : ''}}">
	    <label for="comparison" control-label>{{ 'Comparison' }}</label>
	    <div>
	        <select name="comparison" class="form-control" id="comparison" required>
			    @foreach (App\Models\AlertRuleFormula::$comparisons as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertruleformula->comparison) && $alertruleformula->comparison == $optionKey) ? 'selected' : ''}}>{{ $optionValue }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('comparison', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('comparator') ? 'has-error' : ''}}">
	    <label for="comparator" control-label>{{ 'Comparator' }}</label>
	    <div>
	        <select name="comparator" class="form-control" id="comparator" required>
			    @foreach (App\Models\AlertRuleFormula::$comparators as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertruleformula->comparator) && $alertruleformula->comparator == $optionKey) ? 'selected' : ''}}>{{ $optionKey }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('comparator', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-3">
	<div class="form-group {{ $errors->has('logical') ? 'has-error' : ''}}">
	    <label for="logical" control-label>{{ 'Calculation Minutes' }}</label>
	    <div>
	        <select name="logical" class="form-control" id="logical" required>
			    @foreach (App\Models\AlertRuleFormula::$logicals as $optionKey => $optionValue)
			        <option value="{{ $optionKey }}" {{ (isset($alertruleformula->logical) && $alertruleformula->logical == $optionKey) ? 'selected' : ''}}>{{ $optionValue }}</option>
			    @endforeach
			</select>
	        {!! $errors->first('logical', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('period_minutes') ? 'has-error' : ''}}">
	    <label for="period_minutes" control-label>{{ 'Period Minutes' }}</label>
	    <div>
	        <input class="form-control" name="period_minutes" type="number" id="period_minutes" value="{{ $alertruleformula->period_minutes ?? ''}}" >
	        {!! $errors->first('period_minutes', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('threshold_value') ? 'has-error' : ''}}">
	    <label for="threshold_value" control-label>{{ 'Threshold Value' }}</label>
	    <div>
	        <input class="form-control" name="threshold_value" type="number" id="threshold_value" value="{{ $alertruleformula->threshold_value ?? ''}}" required>
	        {!! $errors->first('threshold_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
