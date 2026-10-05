@extends('layouts.app')

@section('page-title') {{ __('crud.edit').' '.__('general.group') }}
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
	{{ html()->modelForm($item, 'PATCH', route('groups.update', $item->id))->open() }}
	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                {{ html()->text('name')->placeholder(__('crud.name'))->class('form-control') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.type') }}:</label>
                {{ html()->text('type')->placeholder(__('crud.type'))->class('form-control')->style('height:100px') }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
				<button type="submit" class="btn btn-primary btn-block">{{ __('crud.save') }}</button>
        </div>
	</div>
	{{ html()->closeModelForm() }}
@endsection