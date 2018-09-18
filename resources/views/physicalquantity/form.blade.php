<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label">{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $physicalquantity->name or ''}}" required>
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('unit') ? 'has-error' : ''}}">
	    <label for="unit" control-label">{{ 'Unit' }}</label>
	    <div>
	        <input class="form-control" name="unit" type="text" id="unit" value="{{ $physicalquantity->unit or ''}}" required>
	        {!! $errors->first('unit', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('abbreviation') ? 'has-error' : ''}}">
	    <label for="abbreviation" control-label">{{ 'Abbreviation' }}</label>
	    <div>
	        <input class="form-control" name="abbreviation" type="text" id="abbreviation" value="{{ $physicalquantity->abbreviation or ''}}" >
	        {!! $errors->first('abbreviation', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<br>
<div class="form-group">
    <div class="col-xs-12">
        <input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText or 'Create' }}">
    </div>
</div>
