<div class="col-xs-12">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User Id' }}</label>
	    <div>
	        <input class="form-control" name="user_id" type="number" id="user_id" value="{{ $dashboardgroup->user_id ?? ''}}" required>
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('code') ? 'has-error' : ''}}">
	    <label for="code" control-label>{{ 'Code' }}</label>
	    <div>
	        <input class="form-control" name="code" type="text" id="code" value="{{ $dashboardgroup->code ?? ''}}" >
	        {!! $errors->first('code', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $dashboardgroup->name ?? ''}}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_ids') ? 'has-error' : ''}}">
	    <label for="hive_ids" control-label>{{ 'HivesIds' }}</label>
	    {!! Form::select('hive_ids[]', $hive_ids, $dashboardgroup->hive_ids, array('class' => 'form-control select2','multiple')) !!}
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('speed') ? 'has-error' : ''}}">
	    <label for="speed" control-label>{{ 'Speed (seconds)' }}</label>
	    <div>
	        <input class="form-control" name="speed" type="number" id="speed" value="{{ $dashboardgroup->speed ?? 10}}" required>
	        {!! $errors->first('speed', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('interval') ? 'has-error' : ''}}">
	    <label for="interval" control-label>{{ 'Interval' }}</label>
	    <div>
	        {!! Form::select('interval', \App\Models\DashboardGroup::$intervals, $dashboardgroup->interval, array('class' => 'form-control select2','required')) !!}
	        {!! $errors->first('interval', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('show_inspections') ? 'has-error' : ''}}">
	    <label for="show_inspections" control-label>{{ 'Show Inspections' }}</label>
	    <div>
	        <div class="radio">
    <label><input name="show_inspections" type="radio" value="1" {{ (isset($dashboardgroup) && 1 == $dashboardgroup->show_inspections) ? 'checked' : '' }}> Yes</label>
</div>
<div class="radio">
    <label><input name="show_inspections" type="radio" value="0" @if (isset($dashboardgroup)) {{ (0 == $dashboardgroup->show_inspections) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
</div>
	        {!! $errors->first('show_inspections', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('show_all') ? 'has-error' : ''}}">
	    <label for="show_all" control-label>{{ 'Show All' }}</label>
	    <div>
	        <div class="radio">
    <label><input name="show_all" type="radio" value="1" {{ (isset($dashboardgroup) && 1 == $dashboardgroup->show_all) ? 'checked' : '' }}> Yes</label>
</div>
<div class="radio">
    <label><input name="show_all" type="radio" value="0" @if (isset($dashboardgroup)) {{ (0 == $dashboardgroup->show_all) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
</div>
	        {!! $errors->first('show_all', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('hide_measurements') ? 'has-error' : ''}}">
	    <label for="hide_measurements" control-label>{{ 'Hide Measurements' }}</label>
	    <div>
	        <div class="radio">
    <label><input name="hide_measurements" type="radio" value="1" {{ (isset($dashboardgroup) && 1 == $dashboardgroup->hide_measurements) ? 'checked' : '' }}> Yes</label>
</div>
<div class="radio">
    <label><input name="hide_measurements" type="radio" value="0" @if (isset($dashboardgroup)) {{ (0 == $dashboardgroup->hide_measurements) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
</div>
	        {!! $errors->first('hide_measurements', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('logo_url') ? 'has-error' : ''}}">
	    <label for="logo_url" control-label>{{ 'Logo Url' }}</label>
	    <div>
	        <input class="form-control" name="logo_url" type="text" id="logo_url" value="{{ $dashboardgroup->logo_url ?? ''}}" >
	        {!! $errors->first('logo_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
