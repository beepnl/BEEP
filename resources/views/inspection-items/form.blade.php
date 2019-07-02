<div class="col-xs-12">
	<div class="form-group {{ $errors->has('value') ? 'has-error' : ''}}">
	    <label for="value" control-label>{{ 'Value' }}</label>
	    <div>
	        <input class="form-control" name="value" type="text" id="value" value="{{ $inspectionitem->value or ''}}" >
	        {!! $errors->first('value', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('inspection_id') ? 'has-error' : ''}}">
	    <label for="inspection_id" control-label>{{ 'Inspection Id' }}</label>
	    <div>
	        <input class="form-control" name="inspection_id" type="number" id="inspection_id" value="{{ $inspectionitem->inspection_id or ''}}" >
	        {!! $errors->first('inspection_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('category_id') ? 'has-error' : ''}}">
	    <label for="category_id" control-label>{{ 'Category Id' }}</label>
	    <div>
	        <input class="form-control" name="category_id" type="number" id="category_id" value="{{ $inspectionitem->category_id or ''}}" >
	        {!! $errors->first('category_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText or 'Create' }}">
    </div>
</div>
