@extends('layouts.app')

@section('page-title') {{ __('crud.create',['item'=>__('general.user')]) }}
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
	{{ html()->form('POST', route('users.store'))->acceptsFiles()->open() }}
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-4">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                {{ html()->text('name')->placeholder(__('crud.name'))->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-4">
            <div class="form-group">
                <label>{{ __('crud.email') }}:</label>
                {{ html()->text('email')->placeholder(__('crud.email'))->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-4">
            <div class="form-group">
                <label>{{ __('general.Language') }}:</label>
                {{ html()->text('locale')->placeholder(__('general.Language') . ' code')->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <div class="form-group">
                <label>Rate limit (max req/min):</label>
                {{ html()->text('rate_limit_per_min')->placeholder('Max requests per minute' . ' code')->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.avatar') }}:</label>
                {{ html()->file('avatar')->class('btn btn-default') }}
                <p class="help-block">{{ __('crud.avatar_file') }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>{{ __('crud.pass') }}:</label>
                {{ html()->password('password')->attribute('placeholder', __('crud.pass'))->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>{{ __('crud.pass_confirm') }}:</label>
                {{ html()->password('confirm-password')->attribute('placeholder', __('crud.pass_confirm'))->class('form-control') }}
            </div>
        </div>
        @role('superadmin')
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Sensors') }}:</label>
                {{ html()->multiselect('sensors[]', $sensors)->class('form-control select2') }}
                <p class="help-block">{{ __('crud.select_multi', ['item'=>__('general.sensors')]) }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Role') }}:</label>
                {{ html()->multiselect('roles[]', $roles)->class('form-control select2') }}
                <p class="help-block">{{ __('crud.select_multi', ['item'=>__('general.roles')]) }}</p>
            </div>
        </div>
        @endrole
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
				<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
	</div>
    {{ html()->hidden('api-token', Str::random(60)) }}
	{{ html()->form()->close() }}
@endsection