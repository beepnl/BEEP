@extends('layouts.app')
 
@section('page-title') {{ __('general.Group') }}
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                <p>{{ $item->name }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.type') }}:</label>
                <p>{{ $item->type }}</p>
            </div>
        </div>
	</div>
@endsection