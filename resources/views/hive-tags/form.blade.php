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
	<div class="form-group {{ $errors->has('action_id') ? 'has-error' : ''}}">
	    <label for="action_id" control-label>{{ 'Action Id (App code)' }}</label>
	    <div>
	        <input class="form-control" name="action_id" type="number" id="action_id" value="{{ $hivetag->action_id ?? ''}}" >
	        {!! $errors->first('action_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('router_link') ? 'has-error' : ''}}">
	    <label for="router_link" control-label>{{ 'Router Link' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="router_link" type="textarea" id="router_link" >{{ $hivetag->router_link ?? ''}}</textarea>
	        {!! $errors->first('router_link', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
