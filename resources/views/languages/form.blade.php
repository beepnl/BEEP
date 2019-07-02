<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $language->name }}" required>
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('name_english') ? 'has-error' : ''}}">
	    <label for="name_english" control-label>{{ 'Name English' }}</label>
	    <div>
	        <input class="form-control" name="name_english" type="text" id="name_english" value="{{ $language->name_english }}" required>
	        {!! $errors->first('name_english', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('icon') ? 'has-error' : ''}}">
	    <label for="icon" control-label>{{ 'Icon' }}</label>
	    <div>
	        <input class="form-control" name="icon" type="text" id="icon" value="{{ $language->icon }}" >
	        {!! $errors->first('icon', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('abbreviation') ? 'has-error' : ''}}">
	    <label for="abbreviation" control-label>{{ 'Abbreviation' }}</label>
	    <div>
	        <input class="form-control" name="abbreviation" type="text" id="abbreviation" value="{{ $language->abbreviation }}" required>
	        {!! $errors->first('abbreviation', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('twochar') ? 'has-error' : ''}}">
	    <label for="twochar" control-label>{{ 'Abbreviation two characters' }}</label>
	    <div>
	        <input class="form-control" name="twochar" type="text" id="twochar" value="{{ $language->twochar }}" required>
	        {!! $errors->first('twochar', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText }}">
    </div>
</div>
