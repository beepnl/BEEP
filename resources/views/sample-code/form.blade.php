<div class="col-xs-12">
	<div class="form-group {{ $errors->has('sample_code') ? 'has-error' : ''}}">
	    <label for="sample_code" control-label>{{ 'Sample Code' }}</label>
	    <div>
	        <input class="form-control" name="sample_code" type="text" id="sample_code" value="{{ $samplecode->sample_code ?? ''}}" >
	        {!! $errors->first('sample_code', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('sample_note') ? 'has-error' : ''}}">
	    <label for="sample_note" control-label>{{ 'Sample Note' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="sample_note" type="textarea" id="sample_note" >{{ $samplecode->sample_note ?? ''}}</textarea>
	        {!! $errors->first('sample_note', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('sample_date') ? 'has-error' : ''}}">
	    <label for="sample_date" control-label>{{ 'Sample Date' }}</label>
	    <div>
	        <input class="form-control" name="sample_date" type="datetime-local" id="sample_date" value="{{ $samplecode->sample_date ?? ''}}" >
	        {!! $errors->first('sample_date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('test_result') ? 'has-error' : ''}}">
	    <label for="test_result" control-label>{{ 'Test Result' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="test_result" type="textarea" id="test_result" >{{ $samplecode->test_result ?? ''}}</textarea>
	        {!! $errors->first('test_result', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('test') ? 'has-error' : ''}}">
	    <label for="test" control-label>{{ 'Test' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="test" type="textarea" id="test" >{{ $samplecode->test ?? ''}}</textarea>
	        {!! $errors->first('test', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('test_date') ? 'has-error' : ''}}">
	    <label for="test_date" control-label>{{ 'Test Date' }}</label>
	    <div>
	        <input class="form-control" name="test_date" type="datetime-local" id="test_date" value="{{ $samplecode->test_date ?? ''}}" >
	        {!! $errors->first('test_date', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('test_lab_name') ? 'has-error' : ''}}">
	    <label for="test_lab_name" control-label>{{ 'Test Lab Name' }}</label>
	    <div>
	        <textarea class="form-control" rows="5" name="test_lab_name" type="textarea" id="test_lab_name" >{{ $samplecode->test_lab_name ?? ''}}</textarea>
	        {!! $errors->first('test_lab_name', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User' }}</label>
	    <div>
	        {!! Form::select('user_id', App\User::selectList(), isset($samplecode->user_id) ? $samplecode->user_id : null, array('id'=>'user_id', 'class' => 'form-control')) !!}
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_id') ? 'has-error' : ''}}">
	    <label for="hive_id" control-label>{{ 'Hive' }}</label>
	    <div>
	        <input class="form-control" name="hive_id" value="{{ isset($samplecode->hive_id) ? $samplecode->hive_id : '' }}" id="hive_id">
	        {!! $errors->first('hive_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('queen_id') ? 'has-error' : ''}}">
	    <label for="queen_id" control-label>{{ 'Queen' }}</label>
	    <div>
	        {!! Form::select('queen_id', App\Queen::selectList(), isset($samplecode->queen_id) ? $samplecode->queen_id : null, array('id'=>'queen_id', 'class' => 'form-control')) !!}
	        {!! $errors->first('queen_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
