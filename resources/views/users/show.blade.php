@extends('layouts.app')

@section('page-title') {{ __('general.User') }}
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                <p>{{ $user->name }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.email') }}:</label>
                <p>{{ $user->email }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Language') }}:</label>
                <p>{{ $user->locale }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.avatar') }}:</label>
                <br>
                <img src="{{ $user->avatar }}" style="width:100px; height:100px; margin-right: 20px; margin-bottom: 10px;" class="img-circle">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Sensors') }}:</label>
                @if(!empty($sensors))
                    <p>
                    @foreach($sensors as $key => $name)
                        <label class="label label-default">{{ $name }}</label>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Roles') }}:</label>
                @if(!empty($user->roles))
                    <p>
					@foreach($user->roles as $v)
						<label class="label label-warning">{{ $v->display_name }}</label>
					@endforeach
                    </p>
                @endif
            </div>
        </div>
        @role('superadmin')
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>API token:</label>
                <p>{{ $user->api_token }}</p>
            </div>
        </div>
        @endrole
	</div>
@endsection