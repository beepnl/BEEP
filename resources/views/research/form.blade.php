<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'Owner' }}</label>
	    <div>
	        {{-- <input class="form-control" name="checklist_id" type="number" id="checklist_id" value="{{ isset($research->checklist_id) ? $research->checklist_id : '' }}" > --}}
	        {!! Form::select('user_id', App\User::selectList(), isset($research->user_id) ? $research->user_id : Auth::user()->id, array('id'=>'user_id','class' => 'form-control select2')) !!}
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ isset($research->name) ? $research->name : '' }}" >

	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('url') ? 'has-error' : ''}}">
	    <label for="url" control-label>{{ 'URL' }}</label>
	    <div>
	        <input class="form-control" name="url" type="text" id="url" value="{{ isset($research->url) ? $research->url : '' }}" >

	        {!! $errors->first('url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<br>
<hr>
<br>


<div class="col-xs-12">
	<div class="form-group {{ $errors->has('image') ? 'has-error' : ''}}">
	    <div class="row">
	        <div class="col-xs-6">
	    		<label for="image" control-label>{{ 'Upload new image' }}</label>
		        <input class="form-control" name="image" type="file" id="image" >
	        	{!! $errors->first('image', '<p class="help-block">:message</p>') !!}
		    </div>
	        <div class="col-xs-6">
        		<label for="image" control-label>{{ 'Current image' }}</label><br>
        		<img src="{{ isset($research->image_id) ? $research->thumb_url : '' }}" style="width:40px; height: 40px; border-radius: 20%; border: 1px solid #333; display: inline-block; overflow: hidden;">
		    </div>
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('description') ? 'has-error' : ''}}">
	    <label for="description" control-label>{{ 'Description' }}</label>
	    <div>
	        <input class="form-control" name="description" type="text" id="description" value="{{ isset($research->description) ? $research->description : '' }}" >

	        {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<br>
<hr>
<br>

<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
	    <label for="type" control-label>{{ 'Type' }}</label>
	    <div>
	        <input class="form-control" name="type" type="text" id="type" value="{{ isset($research->type) ? $research->type : '' }}" >

	        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('institution') ? 'has-error' : ''}}">
	    <label for="institution" control-label>{{ 'Institution' }}</label>
	    <div>
	        <input class="form-control" name="institution" type="text" id="institution" value="{{ isset($research->institution) ? $research->institution : '' }}" >

	        {!! $errors->first('institution', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('type_of_data_used') ? 'has-error' : ''}}">
	    <label for="type_of_data_used" control-label>{{ 'Type Of Data Used' }}</label>
	    <div>
	        <input class="form-control" name="type_of_data_used" type="text" id="type_of_data_used" value="{{ isset($research->type_of_data_used) ? $research->type_of_data_used : '' }}" >

	        {!! $errors->first('type_of_data_used', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('start_date') ? 'has-error' : ''}}">
	    <label for="start_date" control-label>{{ 'Start Date' }}</label>
	    <div>
	        <input class="form-control" name="start_date" type="date" id="start_date" value="{{ isset($research->start_date) ? substr($research->start_date, 0, 10) : '' }}" >
	        {!! $errors->first('start_date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-4">
	<div class="form-group {{ $errors->has('end_date') ? 'has-error' : ''}}">
	    <label for="end_date" control-label>{{ 'End Date' }}</label>
	    <div>
	        <input class="form-control" name="end_date" type="date" id="end_date" value="{{ isset($research->end_date) ? substr($research->end_date, 0, 10) : '' }}" >
	        {!! $errors->first('end_date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12 col-md-2">
	<div class="form-group {{ $errors->has('visible') ? 'has-error' : ''}}">
	    <label for="visible" control-label>{{ 'Visible in the app' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="visible" type="radio" value="1" @if (isset($research)) {{ (1 == $research->visible) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="visible" type="radio" value="0" {{ (isset($research) && 0 == $research->visible) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('visible', '<p class="help-block">:message</p>') !!}
    </div>
</div>
<div class="col-xs-12 col-md-2">
	<div class="form-group {{ $errors->has('on_invite_only') ? 'has-error' : ''}}">
	    <label for="on_invite_only" title="(only for Most important users)" control-label>{{ 'On invite only' }}</label>
	    <div>
	        <div class="radio">
    			<label><input name="on_invite_only" type="radio" value="1" @if (isset($research)) {{ (1 == $research->on_invite_only) ? 'checked' : '' }} @else {{ 'checked' }} @endif> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="on_invite_only" type="radio" value="0" {{ (isset($research) && 0 == $research->on_invite_only) ? 'checked' : '' }}> No</label>
			</div>
        </div>
        {!! $errors->first('on_invite_only', '<p class="help-block">:message</p>') !!}
    </div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('checklist_ids') ? 'has-error' : ''}}">
	    <label for="checklist_ids" control-label>{{ 'Checklists' }}</label>
	    <div>
	        {{-- <input class="form-control" name="checklist_id" type="number" id="checklist_id" value="{{ isset($research->checklist_id) ? $research->checklist_id : '' }}" > --}}
	        {!! Form::select('checklist_ids[]', App\Checklist::selectList(), $research->checklists->count() > 0 ? $research->checklists->pluck('id') : null, array('id'=>'checklist_ids','class' => 'form-control select2', 'multiple')) !!}
	        {!! $errors->first('checklist_ids', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('viewer_ids') ? 'has-error' : ''}}">
	    <label for="viewer_ids" control-label>{{ 'Viewers' }}</label>
	    <div>
	        {{-- <input class="form-control" name="checklist_id" type="number" id="checklist_id" value="{{ isset($research->checklist_id) ? $research->checklist_id : '' }}" > --}}
	        {!! Form::select('viewer_ids[]', App\User::selectList(), $research->viewers->count() > 0 ? $research->viewers->pluck('id') : null, array('id'=>'viewer_ids','class' => 'form-control select2', 'multiple')) !!}
	        {!! $errors->first('viewer_ids', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('default_user_ids') ? 'has-error' : ''}}">
	    <label for="default_user_ids" control-label>{{ 'Most important users (first selection in research backend) & Invited users (in case of On invite only)' }}</label>
	    <div>
	        {{-- <input class="form-control" name="checklist_id" type="number" id="checklist_id" value="{{ isset($research->checklist_id) ? $research->checklist_id : '' }}" > --}}
	        {!! Form::select('default_user_ids[]', App\User::selectList(), isset($research->default_user_ids) ? $research->default_user_ids : null, array('id'=>'default_user_ids','class' => 'form-control select2', 'multiple')) !!}
	        {!! $errors->first('default_user_ids', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ isset($submitButtonText) ? $submitButtonText : 'Create' }}">
    </div>
</div>
