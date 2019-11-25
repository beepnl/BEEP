<div class="col-xs-12">
	<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
	    <label for="name" control-label>{{ 'Name' }}</label>
	    <div>
	        <input class="form-control" name="name" type="text" id="name" value="{{ isset($research->name) ? $research->name : '' }}" >

	        {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('url') ? 'has-error' : ''}}">
	    <label for="url" control-label>{{ 'URL' }}</label>
	    <div>
	        <input class="form-control" name="url" type="text" id="url" value="{{ isset($research->url) ? $research->url : '' }}" >

	        {!! $errors->first('url', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('image') ? 'has-error' : ''}}">
	    <label for="image" control-label>{{ 'Image' }}</label>
	    <div>
	        <image src="{{ isset($research->image) ? $research->image : ''}}">
	        <input class="form-control" name="image" type="file" id="image" >

	        {!! $errors->first('image', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('description') ? 'has-error' : ''}}">
	    <label for="description" control-label>{{ 'Description' }}</label>
	    <div>
	        <input class="form-control" name="description" type="text" id="description" value="{{ isset($research->description) ? $research->description : '' }}" >

	        {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
	    <label for="type" control-label>{{ 'Type' }}</label>
	    <div>
	        <input class="form-control" name="type" type="text" id="type" value="{{ isset($research->type) ? $research->type : '' }}" >

	        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('institution') ? 'has-error' : ''}}">
	    <label for="institution" control-label>{{ 'Institution' }}</label>
	    <div>
	        <input class="form-control" name="institution" type="text" id="institution" value="{{ isset($research->institution) ? $research->institution : '' }}" >

	        {!! $errors->first('institution', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('type_of_data_used') ? 'has-error' : ''}}">
	    <label for="type_of_data_used" control-label>{{ 'Type Of Data Used' }}</label>
	    <div>
	        <input class="form-control" name="type_of_data_used" type="text" id="type_of_data_used" value="{{ isset($research->type_of_data_used) ? $research->type_of_data_used : '' }}" >

	        {!! $errors->first('type_of_data_used', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('start_date') ? 'has-error' : ''}}">
	    <label for="start_date" control-label>{{ 'Start Date' }}</label>
	    <div>
	        <input class="form-control" name="start_date" type="datetime" id="start_date" value="{{ isset($research->start_date) ? $research->start_date : '' }}" >
	        {!! $errors->first('start_date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('end_date') ? 'has-error' : ''}}">
	    <label for="end_date" control-label>{{ 'End Date' }}</label>
	    <div>
	        <input class="form-control" name="end_date" type="datetime" id="end_date" value="{{ isset($research->end_date) ? $research->end_date : '' }}" >
	        {!! $errors->first('end_date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('checklist_id') ? 'has-error' : ''}}">
	    <label for="checklist_id" control-label>{{ 'Checklist Id' }}</label>
	    <div>
	        {{-- <input class="form-control" name="checklist_id" type="number" id="checklist_id" value="{{ isset($research->checklist_id) ? $research->checklist_id : '' }}" > --}}
	        {!! Form::select('checklist_id', App\Checklist::selectList(), isset($research->checklist_id) ? $research->checklist_id : null, array('id'=>'checklist_id','class' => 'form-control', 'placeholder'=>__('crud.select',['item'=>__('beep.checklist')]).'...')) !!}
	        {!! $errors->first('checklist_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ isset($submitButtonText) ? $submitButtonText : 'Create' }}">
    </div>
</div>
