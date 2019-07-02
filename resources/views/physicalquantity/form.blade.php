<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ isset($physicalquantity->name) ? $physicalquantity->name : '' }}" required>
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('unit') ? 'has-error' : ''}}">
	    <label for="unit" control-label>{{ 'Unit' }}</label>
	    <div>
	        <input class="form-control" name="unit" type="text" id="unit" value="{{ isset($physicalquantity->unit) ? $physicalquantity->unit : '' }}" required>
	        {!! $errors->first('unit', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('abbreviation') ? 'has-error' : ''}}">
	    <label for="abbreviation" control-label>{{ 'Abbreviation' }}</label>
	    <div>
	        <input class="form-control" name="abbreviation" type="text" id="abbreviation" value="{{ isset($physicalquantity->abbreviation) ? $physicalquantity->abbreviation : '' }}" >
	        {!! $errors->first('abbreviation', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('low_value') ? 'has-error' : ''}}">
	    <label for="low_value" control-label>{{ 'Low value (human as reference)' }}</label>
	    <div>
	        <input class="form-control" name="low_value" type="text" id="low_value" value="{{ isset($physicalquantity->low_value) ? $physicalquantity->low_value : '' }}" >
	        {!! $errors->first('low_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('high_value') ? 'has-error' : ''}}">
	    <label for="high_value" control-label>{{ 'High value (human as reference)' }}</label>
	    <div>
	        <input class="form-control" name="high_value" type="text" id="high_value" value="{{ isset($physicalquantity->high_value) ? $physicalquantity->high_value : '' }}" >
	        {!! $errors->first('high_value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ isset($submitButtonText) ? $submitButtonText : 'Create' }}">
    </div>
</div>
