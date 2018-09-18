<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label">{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $hivetype->name or ''}}" required>
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
	    <label for="type" control-label">{{ 'Type' }}</label>
	    <div>
	        <input class="form-control" name="type" type="text" id="type" value="{{ $hivetype->type or ''}}" required>
	        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('image') ? 'has-error' : ''}}">
	    <label for="image" control-label">{{ 'Image' }}</label>
	    <div>
	        <input class="form-control" name="image" type="text" id="image" value="{{ $hivetype->image or ''}}" >
	        {!! $errors->first('image', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('continents') ? 'has-error' : ''}}">
	    <label for="continents" control-label">{{ 'Continents' }}</label>
	    <div>
	        <input class="form-control" name="continents" type="text" id="continents" value="{{ $hivetype->continents or ''}}" >
	        {!! $errors->first('continents', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('info_url') ? 'has-error' : ''}}">
	    <label for="info_url" control-label">{{ 'Info Url' }}</label>
	    <div>
	        <input class="form-control" name="info_url" type="text" id="info_url" value="{{ $hivetype->info_url or ''}}" >
	        {!! $errors->first('info_url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<br>
<div class="form-group">
    <div class="col-xs-12">
        <input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText or 'Create' }}">
    </div>
</div>
