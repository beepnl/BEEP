@extends('layouts.app')

@section('page-title') {{ __('crud.create',['item'=>__('general.role')]) }}
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
	{{ html()->form('POST', route('roles.store'))->open() }}
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                {{ html()->text('name')->placeholder(__('crud.name'))->class('form-control') }}
            </div>
        </div>
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.display_name') }}:</label>
                {{ html()->text('display_name')->placeholder(__('crud.display_name'))->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.description') }}:</label>
                {{ html()->textarea('description')->placeholder(__('crud.description'))->class('form-control')->style('height:100px') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Permissions') }}:</label>
                <p class="help-block">{{ __('crud.select_multi', ['item'=>__('general.permission')]) }}</p>
                <p>
                @foreach($permission as $value)
                	<label>{{ html()->checkbox('permission[]', false, $value->id)->class('name') }}
                	{{ $value->display_name }}</label>
                	<br/>
                @endforeach
                </p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
			<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
	</div>
	{{ html()->form()->close() }}
@endsection