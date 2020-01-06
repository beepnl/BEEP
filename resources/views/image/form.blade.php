<div class="col-xs-12">
	<div class="form-group {{ $errors->has('file') ? 'has-error' : ''}}">
	    <label for="file" control-label>{{ 'File' }}</label>
	    <div>
	        <input class="form-control" name="file" type="file" id="file" value="{{ $image->file }}" >
	        {!! $errors->first('file', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
	@if(isset($image->thumb_url))
		<img src="{{ $item->thumb_url }}">
	@endif
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('description') ? 'has-error' : ''}}">
	    <label for="description" control-label>{{ 'Description' }}</label>
	    <div>
	        <input class="form-control" name="description" type="text" id="description" value="{{ $image->description }}" >
	        {!! $errors->first('description', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('type') ? 'has-error' : ''}}">
	    <label for="type" control-label>{{ 'Type' }}</label>
	    <div>
	        <input class="form-control" name="type" type="text" id="type" value="{{ $image->type }}" >
	        {!! $errors->first('type', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('height') ? 'has-error' : ''}}">
	    <label for="height" control-label>{{ 'Height' }}</label>
	    <div>
	        <input class="form-control" name="height" type="number" id="height" value="{{ $image->height }}" >
	        {!! $errors->first('height', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('width') ? 'has-error' : ''}}">
	    <label for="width" control-label>{{ 'Width' }}</label>
	    <div>
	        <input class="form-control" name="width" type="number" id="width" value="{{ $image->width }}" >
	        {!! $errors->first('width', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('size_kb') ? 'has-error' : ''}}">
	    <label for="size_kb" control-label>{{ 'Size Kb' }}</label>
	    <div>
	        <input class="form-control" name="size_kb" type="number" id="size_kb" value="{{ $image->size_kb }}" >
	        {!! $errors->first('size_kb', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('date') ? 'has-error' : ''}}">
	    <label for="date" control-label>{{ 'Date' }}</label>
	    <div>
	        <input class="form-control" name="date" type="datetime-local" id="date" value="{{ $image->date }}" >
	        {!! $errors->first('date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User' }}</label>
	    <div>
	        {!! Form::select('user_id', App\User::all()->sortBy('name')->pluck('name','id'), isset($image->user_id) ? $image->user_id : Auth::user()->id, array('id'=>'user_id', 'class' => 'form-control select2', 'placeholder'=>'Select user...')) !!}
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_id') ? 'has-error' : ''}}">
	    <label for="hive_id" control-label>{{ 'Hive' }}</label>
	    <div>
	        {!! Form::select('hive_id', Auth::user()->allHives()->orderBy('name')->pluck('name','id'), isset($image->hive_id) ? $image->hive_id : null, array('id'=>'hive_id', 'class' => 'form-control select2', 'placeholder'=>'Select hive...')) !!}
	        {!! $errors->first('hive_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('category_id') ? 'has-error' : ''}}">
	    <label for="category_id" control-label>{{ 'Category (image, file)' }}</label>
	    <div>
	        {!! Form::select('category_id', App\Category::all()->whereIn('input', ['image','file'])->pluck('trans','id'), isset($image->category_id) ? $image->category_id : null, array('id'=>'category_id', 'class' => 'form-control select2', 'placeholder'=>'Select category...')) !!}
	        {!! $errors->first('category_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('inspection_id') ? 'has-error' : ''}}">
	    <label for="inspection_id" control-label>{{ 'Inspection' }}</label>
	    <div>
	        {!! Form::select('inspection_id', Auth::user()->inspections->sortByDesc('created_at')->pluck('created_at','id'), isset($image->inspection_id) ? $image->inspection_id : null, array('id'=>'inspection_id', 'class' => 'form-control select2', 'placeholder'=>'Select inspection...')) !!}
	        {!! $errors->first('inspection_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>



<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText }}">
    </div>
</div>
