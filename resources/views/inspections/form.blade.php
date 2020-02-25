@php
	if(!isset($inspection))
	{
		$inspection = new App\Inspection();
	}
@endphp
<div class="col-xs-12">
	<div class="form-group {{ $errors->has('notes') ? 'has-error' : ''}}">
	    <label for="notes" control-label>{{ 'Notes' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="notes" type="textarea" id="notes" >{{ $inspection->notes ?? ''}}</textarea>
	        {!! $errors->first('notes', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('impression') ? 'has-error' : ''}}">
	    <label for="impression" control-label>{{ 'Impression' }}</label>
	    <div>
	        <input class="form-control" name="impression" type="number" id="impression" value="{{ $inspection->impression ?? ''}}" >
	        {!! $errors->first('impression', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('attention') ? 'has-error' : ''}}">
	    <label for="attention" control-label>{{ 'Attention' }}</label>
	    <div>
	        <div class="radio">
			    <label>
			    	<input name="attention" type="radio" value="1" {{ (isset($inspection) && 1 == $inspection->attention) ? 'checked' : '' }}> Yes
			    </label>
			</div>
			<div class="radio">
			    <label>
			    	<input name="attention" type="radio" value="0" @if (isset($inspection)) {{ (0 == $inspection->attention) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
			</div>
	        {!! $errors->first('attention', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('created_at') ? 'has-error' : ''}}">
	    <label for="created_at" control-label>{{ 'Created At' }}</label>
	    <div>
	        <input class="form-control" name="created_at" type="text" id="created_at" value="{{ $inspection->created_at ?? ''}}" >
	        {!! $errors->first('created_at', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12">
	<div class="form-group {{ $errors->has('checklist_id') ? 'has-error' : ''}}">
	    <label for="checklist_id" control-label>{{ 'Checklist' }}</label>
	    <div>
	        {!! Form::select('checklist_id', App\Checklist::selectList(), isset($measurement->checklist_id) ? $measurement->checklist_id : null, array('id'=>'checklist_id', 'class' => 'form-control select2')) !!}
	        {!! $errors->first('checklist_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>

<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
