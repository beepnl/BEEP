<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label">{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $beerace->name or ''}}" required>
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
	    <label for="type" control-label">{{ 'Type' }}</label>
	    <div>
	        <input class="form-control" name="type" type="text" id="type" value="{{ $beerace->type or ''}}" required>
	        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('synonyms') ? 'has-error' : ''}}">
	    <label for="synonyms" control-label">{{ 'Synonyms' }}</label>
	    <div>
	        <input class="form-control" name="synonyms" type="text" id="synonyms" value="{{ $beerace->synonyms or ''}}" >
	        {!! $errors->first('synonyms', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('continents') ? 'has-error' : ''}}">
	    <label for="continents" control-label">{{ 'Continents' }}</label>
	    <div>
	        <input class="form-control" name="continents" type="text" id="continents" value="{{ $beerace->continents or ''}}" >
	        {!! $errors->first('continents', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<br>
<div class="form-group">
    <div class="col-xs-12">
        <input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText or 'Create' }}">
    </div>
</div>
