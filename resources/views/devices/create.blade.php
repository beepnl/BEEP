@extends('layouts.app')

@section('page-title') {{ __('crud.create',['item'=>__('general.device')]) }}
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
	{!! Form::open(array('route' => 'devices.store','method'=>'POST')) !!}
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                {!! Form::text('name', null, ['placeholder' => __('crud.name'),'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.type') }}:</label>
                {!! Form::select('category_id', $types, null, ['placeholder'=>__('crud.select', ['item'=>__('general.device').' '.__('general.type')]),'class' => 'form-control select2']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>LoRa DEV EUI:</label>
                {!! Form::text('key', null, ['placeholder' => __('crud.key'),'class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.User') }}:</label>
                {!! Form::select('user_id', App\User::selectlist(), null, array('placeholder'=>__('crud.select', ['item'=>__('general.user')]),'class' => 'form-control select2')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <br>
			<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="form-group {{ $errors->has('rtc') ? 'has-error' : ''}}">
                <label for="rtc" control-label>{{ 'RTC' }}</label>
                <div>
                    <div class="radio">
                        <label><input name="rtc" type="radio" value="1"> Yes</label>
                    </div>
                    <div class="radio">
                        <label><input name="rtc" type="radio" value="0" checked> No</label>
                    </div>
                    {!! $errors->first('rtc', '<p class="help-block">:message</p>') !!}
                </div>
            </div>
        </div>
	</div>
	{!! Form::close() !!}
@endsection