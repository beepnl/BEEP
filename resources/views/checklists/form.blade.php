
	<div class="col-xs-6">
	    <div class="form-group">
	        <label>{{__('general.Categories')}} (Drag &amp; Drop to change the order in the checklist)</label>

	    	<input type="hidden" name="categories" id="categoryinput">
	    	<input class="form-control" type="text" id="checklist-tree-search" placeholder="Search">
		    <div id="checklist-tree">
		        @include('categories.partials.tree', ['categories'=>$taxonomy, 'selected'=>$selected, 'edit_checklist'=>true])
		    </div>
		</div>
	</div>

	<div class="col-xs-6">
		<div class="col-xs-12">
			<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
			    <label for="name" control-label>{{ __('crud.name') }}</label>
			    <div>
			        <input class="form-control" name="name" type="text" id="name" value="{{ $checklist->name }}" >
			        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
			    </div>
			</div>
		</div>
		<div class="col-xs-12">
			<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
			    <label for="type" control-label>{{ __('crud.type') }}</label>
			    <div>
			        <input class="form-control" name="type" type="text" id="type" value="{{ $checklist->type }}" >
			        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
			    </div>
			</div>
		</div>
		<div class="col-xs-12">
			<div class="form-group {{ $errors->has('description') ? 'has-error' : ''}}">
			    <label for="description" control-label>{{ __('crud.description') }}</label>
			    <div>
			        <input class="form-control" name="description" type="text" id="description" value="{{ $checklist->description }}" >
			        {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
			    </div>
			</div>
		</div>

		@role('superadmin')
		<div class="col-xs-12">
			<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
			    <label for="user_id" control-label>{{ __('general.User') }}</label>
			    <div>
			        {!! Form::select('user_id', $users, $selectedUserIds, array('id'=>'user_id', 'multiple'=>'multiple', 'class' => 'form-control', 'placeholder'=>'Select users...')) !!}
			        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
			    </div>
			</div>
		</div>
		@endrole
		
		@if(Auth::user()->hasRole('superadmin') == false)
		<div class="col-xs-12">
			<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
			    <label for="user_id" control-label>{{ __('general.User') }}</label>
			    <div>
			        <p>{{ Auth::user()->name }}</p>
			        {!! Form::hidden('user_id', $selectedUserId) !!}
			        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
			    </div>
			</div>
		</div>
		@endif
	</div>

<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ isset($submitButtonText) ? $submitButtonText : 'Save' }}">
    </div>
</div>
