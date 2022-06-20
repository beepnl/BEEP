<div class="col-xs-12">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User Id' }}</label>
	    <div>
	        <input class="form-control" name="user_id" type="number" id="user_id" value="{{ $hivetag->user_id ?? ''}}" required>
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('tag') ? 'has-error' : ''}}">
	    <label for="tag" control-label>{{ 'Tag' }}</label>
	    <div>
	        <input class="form-control" name="tag" type="text" id="tag" value="{{ $hivetag->tag ?? ''}}" >
	        {!! $errors->first('tag', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_id') ? 'has-error' : ''}}">
	    <label for="hive_id" control-label>{{ 'Hive Id' }}</label>
	    <div>
	        <input class="form-control" name="hive_id" type="number" id="hive_id" value="{{ $hivetag->hive_id ?? ''}}" >
	        {!! $errors->first('hive_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('url') ? 'has-error' : ''}}">
	    <label for="url" control-label>{{ 'Url' }}</label>
	    <div>
	        <input class="form-control" name="url" type="text" id="url" value="{{ $hivetag->url ?? ''}}" >
	        {!! $errors->first('url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('description') ? 'has-error' : ''}}">
	    <label for="description" control-label>{{ 'Description' }}</label>
	    <div>
	        <input class="form-control" name="description" type="text" id="description" value="{{ $hivetag->description ?? ''}}" >
	        {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('router') ? 'has-error' : ''}}">
	    <label for="router" control-label>{{ 'Router' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="router" type="textarea" id="router" >{{ $hivetag->router ?? ''}}</textarea>
	        {!! $errors->first('router', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
