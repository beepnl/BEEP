<div class="col-xs-12">
	<div class="form-group {{ $errors->has('user_id') ? 'has-error' : ''}}">
	    <label for="user_id" control-label>{{ 'User Id' }}</label>
	    <div>
	        {!! Form::select('user_id', App\User::selectList(), isset($flashlog->user_id) ? $flashlog->user_id : null, array('id'=>'user_id', 'class' => 'form-control select2')) !!}
	        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('device_id') ? 'has-error' : ''}}">
	    <label for="device_id" control-label>{{ 'Device Id' }}</label>
	    <div>
	        {!! Form::select('device_id', App\Device::selectList(), isset($flashlog->device_id) ? $flashlog->device_id : null, array('id'=>'device_id', 'class' => 'form-control select2')) !!}
	        {!! $errors->first('device_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('hive_id') ? 'has-error' : ''}}">
	    <label for="hive_id" control-label>{{ 'Hive Id' }}</label>
	    <div>
	        <input class="form-control" name="hive_id" value="{{ isset($flashlog->hive_id) ? $flashlog->hive_id : '' }}" id="hive_id">
	        {!! $errors->first('hive_id', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_messages') ? 'has-error' : ''}}">
	    <label for="log_messages" control-label>{{ 'Log Messages' }}</label>
	    <div>
	        <input class="form-control" name="log_messages" type="number" id="log_messages" value="{{ $flashlog->log_messages ?? ''}}" >
	        {!! $errors->first('log_messages', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_saved') ? 'has-error' : ''}}">
	    <label for="log_saved" control-label>{{ 'Log Saved' }}</label>
	    <div>
	       	<div class="radio">
			    <label><input name="log_saved" type="radio" value="1" {{ (isset($flashlog) && 1 == $flashlog->log_saved) ? 'checked' : '' }}> Yes</label>
			</div>
			<div class="radio">
			    <label><input name="log_saved" type="radio" value="0" @if (isset($flashlog)) {{ (0 == $flashlog->log_saved) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
			</div>
	        {!! $errors->first('log_saved', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_parsed') ? 'has-error' : ''}}">
	    <label for="log_parsed" control-label>{{ 'Log Parsed' }}</label>
	    <div>
	        <div class="radio">
    <label><input name="log_parsed" type="radio" value="1" {{ (isset($flashlog) && 1 == $flashlog->log_parsed) ? 'checked' : '' }}> Yes</label>
</div>
<div class="radio">
    <label><input name="log_parsed" type="radio" value="0" @if (isset($flashlog)) {{ (0 == $flashlog->log_parsed) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
</div>
	        {!! $errors->first('log_parsed', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_has_timestamps') ? 'has-error' : ''}}">
	    <label for="log_has_timestamps" control-label>{{ 'Log Has Timestamps' }}</label>
	    <div>
	        <div class="radio">
    <label><input name="log_has_timestamps" type="radio" value="1" {{ (isset($flashlog) && 1 == $flashlog->log_has_timestamps) ? 'checked' : '' }}> Yes</label>
</div>
<div class="radio">
    <label><input name="log_has_timestamps" type="radio" value="0" @if (isset($flashlog)) {{ (0 == $flashlog->log_has_timestamps) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
</div>
	        {!! $errors->first('log_has_timestamps', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('bytes_received') ? 'has-error' : ''}}">
	    <label for="bytes_received" control-label>{{ 'Bytes Received' }}</label>
	    <div>
	        <input class="form-control" name="bytes_received" type="number" id="bytes_received" value="{{ $flashlog->bytes_received ?? ''}}" >
	        {!! $errors->first('bytes_received', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_file') ? 'has-error' : ''}}">
	    <label for="log_file" control-label>{{ 'Log File' }}</label>
	    <div>
	        <input class="form-control" name="log_file" type="text" id="log_file" value="{{ $flashlog->log_file ?? ''}}" >
	        {!! $errors->first('log_file', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_file_stripped') ? 'has-error' : ''}}">
	    <label for="log_file_stripped" control-label>{{ 'Log File Stripped' }}</label>
	    <div>
	        <input class="form-control" name="log_file_stripped" type="text" id="log_file_stripped" value="{{ $flashlog->log_file_stripped ?? ''}}" >
	        {!! $errors->first('log_file_stripped', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div><div class="col-xs-12">
	<div class="form-group {{ $errors->has('log_file_parsed') ? 'has-error' : ''}}">
	    <label for="log_file_parsed" control-label>{{ 'Log File Parsed' }}</label>
	    <div>
	        <input class="form-control" name="log_file_parsed" type="text" id="log_file_parsed" value="{{ $flashlog->log_file_parsed ?? ''}}" >
	        {!! $errors->first('log_file_parsed', '<p class="help-block">:message</p>') !!}
	    </div>
	</div>
</div>


<div class="col-xs-12" style="margin-top: 20px;">
	<div class="form-group">
    	<input class="btn btn-primary btn-block" type="submit" value="{{ $submitButtonText ?? 'Create' }}">
    </div>
</div>
