@extends('layouts.app')

@section('page-title') {{ __('general.Role') }}
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                <p>{{ $role->name }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.display_name') }}:</label>
                <p>{{ $role->display_name }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.description') }}:</label>
                <p>{{ $role->description }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Permissions') }}:</label>
                @if(!empty($rolePermissions))
                    <p>
					@foreach($rolePermissions as $v)
						<label class="label label-success">{{ $v->display_name }}</label>
					@endforeach
                    </p>
				@endif
            </div>
        </div>
	</div>
@endsection