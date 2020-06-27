<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $categoryinput->name }}" required>
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
	    <label for="type" control-label>{{ 'Type' }}</label>
	    <div>
	        <input class="form-control" name="type" type="text" id="type" value="{{ $categoryinput->type }}" required>
	        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('min') ? 'has-error' : ''}}">
	    <label for="min" control-label>{{ 'Min' }}</label>
	    <div>
	        <input class="form-control" name="min" type="number" id="min" value="{{ $categoryinput->min }}" >
	        {!! $errors->first('min', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('max') ? 'has-error' : ''}}">
	    <label for="max" control-label>{{ 'Max' }}</label>
	    <div>
	        <input class="form-control" name="max" type="number" id="max" value="{{ $categoryinput->max }}" >
	        {!! $errors->first('max', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('decimals') ? 'has-error' : ''}}">
	    <label for="decimals" control-label>{{ 'Decimals' }}</label>
	    <div>
	        <input class="form-control" name="decimals" type="number" id="decimals" value="{{ $categoryinput->decimals }}" >
	        {!! $errors->first('decimals', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('icon') ? 'has-error' : ''}}">
	    <label for="icon" control-label>{{ 'Icon' }}</label>
	    <div>
	        <input class="form-control" name="icon" type="text" id="icon" value="{{ $categoryinput->icon }}" >
	        {!! $errors->first('icon', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<br>
<div class="form-group">
    <div class="col-xs-12">
        <input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
