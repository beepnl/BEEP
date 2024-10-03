@extends('layouts.app')

@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' (ID: '.$research->id.')' }} Research dates: {{ substr($research->start_date, 0, 10) }} - {{ substr($research->end_date, 0, 10) }}
    @permission('role-view')
        <a href="{{ route('research.show', $research->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-primary pull-right"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
    @endpermission
@endsection

@section('content')
    @component('components/box')
    	@slot('title') Research consent {{ $item->id }} for user {{ $item->user_name}} ({{ $item->user_id }})
        @endslot

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

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                    <label>Consent</label>
                    <p>{{ $item->consent }}</p>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                    <label>Created</label>
                    <p>{{ $item->created_at }}</p>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                    <label>Updated</label>
                    <p>{{ $item->updated_at }}</p>
                </div>
            </div>


    	{!! Form::model($item, ['method' => 'PATCH','route' => ['research.consent_edit', ['id'=>$research->id, 'c_id'=>$item->id]]]) !!}

            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                    <label>{{ __('beep.Hives') }}</label>
                    {!! Form::select('consent_hive_ids[]', \App\Hive::where('user_id',$item->user_id)->pluck('name','id')->toArray(), $item->consent_hive_ids, array('class' => 'form-control select2', 'multiple', 'id'=>'consent_hive_ids')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                    <label>Locations</label>
                    {!! Form::select('consent_location_ids[]', \App\Location::where('user_id',$item->user_id)->pluck('name','id')->toArray(), $item->consent_location_ids, array('class' => 'form-control select2', 'multiple', 'id'=>'consent_location_ids')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="form-group">
                    <label>Devices</label>
                    {!! Form::select('consent_sensor_ids[]', \App\Device::where('user_id',$item->user_id)->pluck('name','id')->toArray(), $item->consent_sensor_ids, array('class' => 'form-control select2', 'multiple', 'id'=>'consent_sensor_ids')) !!}
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

    	{!! Form::close() !!}
    @endcomponent
@endsection
