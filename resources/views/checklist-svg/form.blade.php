<div class="col-xs-12">
            <div class="form-group">
                <label>{{ __('general.User') }}</label>
                {!! Form::select('user_id', App\User::selectlist(), isset($checklistsvg->user_id) ? $checklistsvg->user_id : Auth::user()->id, array('placeholder'=>__('crud.select', ['item'=>__('general.user')]),'class' => 'form-control select2')) !!}
            </div>
        </div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('checklist_id') ? 'has-error' : ''}}">
	    <label for="checklist_id" control-label>{{ 'Checklist' }}</label>
	    <div>
	        {!! Form::select('checklist_id', App\Checklist::selectList(), isset($checklistsvg->checklist_id) ? $checklistsvg->checklist_id : null, array('id'=>'checklist_id', 'class' => 'form-control select2')) !!}
	        {!! $errors->first('checklist_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ $checklistsvg->name ?? ''}}" >
	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('app_version') ? 'has-error' : ''}}">
	    <label for="app_version" control-label>{{ 'App version' }}</label>
	    <div>
	        <input class="form-control" name="app_version" type="text" id="app_version" value="{{ $checklistsvg->app_version ?? ''}}" >
	        {!! $errors->first('app_version', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('svg') ? 'has-error' : ''}}">
	    <label for="svg" control-label>{{ 'Svg' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="svg" type="textarea" id="svg" >{{ $checklistsvg->svg ?? ''}}</textarea>
	        {!! $errors->first('svg', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('pages') ? 'has-error' : ''}}">
	    <label for="pages" control-label>{{ 'Pages' }}</label>
	    <div>
	        <input class="form-control" name="pages" type="number" id="pages" value="{{ $checklistsvg->pages ?? ''}}" >
	        {!! $errors->first('pages', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('last_print') ? 'has-error' : ''}}">
	    <label for="last_print" control-label>{{ 'Last Print' }}</label>
	    <div>
	        <input class="form-control" name="last_print" type="datetime-local" id="last_print" value="{{ $checklistsvg->last_print ?? ''}}" >
	        {!! $errors->first('last_print', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
