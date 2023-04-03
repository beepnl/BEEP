@extends('layouts.app')
@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' '.__('general.user') }}
@endsection

@section('content')

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			{{ __('crud.input_err') }}:<br>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif
	{!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id],'files'=>'true']) !!}
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-3">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                {!! Form::text('name', null, array('placeholder' => __('crud.name'),'class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <div class="form-group">
                <label>{{ __('crud.email') }}:</label>
                {!! Form::text('email', null, array('placeholder' => __('crud.email'),'class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <div class="form-group">
                <label>{{ __('general.Language') }}:</label>
                {!! Form::text('locale', null, array('placeholder' => __('general.Language').' code','class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <div class="form-group">
                <label>Rate limit (max req/min):</label>
                {!! Form::text('rate_limit_per_min', null, array('placeholder' => 'Max requests per minute'.' code','class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.avatar') }}:</label>
                <br>
                <img src="{{ $user->avatar }}" style="width:100px; height:100px; margin-right: 20px; margin-bottom: 10px;" class="img-circle">
                {!! Form::file('avatar', array('class' => 'btn btn-default', 'style'=>'display: inline-block;')) !!}
                <p class="help-block">{{ __('crud.avatar_file')}}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>{{ __('crud.pass') }}:</label>
                {!! Form::password('password', array('placeholder' => __('crud.pass'),'class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>{{ __('crud.pass_confirm') }}:</label>
                {!! Form::password('confirm-password', array('placeholder' => __('crud.pass_confirm'),'class' => 'form-control')) !!}
            </div>
        </div>
        @role('superadmin')
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Sensors') }}:</label>
                {!! Form::select('sensors[]', $sensors, $userSensor, array('class' => 'form-control select2','multiple')) !!}
                <p class="help-block">{{ __('crud.select_multi', ['item'=>__('general.sensors')]) }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Role') }}:</label>
                {!! Form::select('roles[]', $roles, $userRole, array('class' => 'form-control select2','multiple')) !!}
                <p class="help-block">{{ __('crud.select_multi', ['item'=>__('general.roles')]) }}</p>
            </div>
        </div>
        @endrole

        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <br>
    		<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
    </div>
	{!! Form::close() !!}
@endsection