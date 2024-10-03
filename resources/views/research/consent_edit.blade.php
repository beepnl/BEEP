@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' Consent' }}
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

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <label>Consent</label>
                <p>{{ $item->consent }}</p>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="form-group">
                <label>Created</label>
                <p>{{ $item->created_at }}</p>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="form-group">
                <label>Updated</label>
                <p>{{ $item->updated_at }}</p>
            </div>
        </div>
    </div>

	{!! Form::model($item, ['method' => 'PATCH','route' => ['research.consent_edit', ['id'=>$research->id, 'c_id'=>$item->id]]]) !!}
	<div class="row">
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>{{ __('beep.Hives') }}</label>
                {!! Form::select('consent_hive_ids[]', \App\Hive::selectList(), $item->consent_hive_ids, array('class' => 'form-control select2', 'multiple')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Locations</label>
                {!! Form::select('consent_location_ids[]', \App\Location::selectList(), $item->consent_location_ids, array('class' => 'form-control select2', 'multiple')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Devices</label>
                {!! Form::select('consent_sensor_ids[]', \App\Device::selectList(), $item->consent_sensor_ids, array('class' => 'form-control select2', 'multiple')) !!}
            </div>
        </div>

        {{-- <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="form-group">
                <label>Created date</label>
                <p>{{ $item->created_at }}</p>
            </div>
        </div> --}}

        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <br>
			<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
	</div>
	{!! Form::close() !!}
@endsection