@extends('layouts.app')
 
@section('page-title') {{ __('crud.management', ['item'=>__('general.sensor')]) }}
@endsection

@section('content')

			
	@component('components/box')
		@slot('title')
			{{ __('crud.overview', ['item'=>__('general.sensors')]) }}
		@endslot

		@slot('action')
			@permission('sensor-create')
	            <a class="btn btn-primary" href="{{ route('sensors.create') }}"><i class="fa fa-plus"></i> {{ __('crud.add', ['item'=>__('general.sensor')]) }}</a>
	            @endpermission
		@endslot

		@slot('body')
			<table class="table table-striped">
				<tr>
					<th>{{ __('crud.id') }}</th>
					<th>{{ __('crud.name') }}</th>
					<th>{{ __('crud.type') }}</th>
					<th>{{ __('crud.key') }}</th>
					<th>{{ __('crud.actions') }}</th>
				</tr>
			@foreach ($sensors as $key => $sensor)
			<tr>
				<td>{{ $sensor->id }}</td>
				<td>{{ $sensor->name }}</td>
				<td><label class="label label-default">{{ $sensor->type }}</label></td>
				<td>{{ $sensor->key }}</td>
				<td>
					<a class="btn btn-default" href="{{ route('sensors.show',$sensor->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a>
					@permission('sensor-edit')
					<a class="btn btn-primary" href="{{ route('sensors.edit',$sensor->id) }}" title="{{ __('crud.edit') }}"><i class="fa fa-pencil"></i></a>
					@endpermission
					@permission('sensor-delete')
					{!! Form::open(['method' => 'DELETE','route' => ['sensors.destroy', $sensor->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("'.__('crud.sure',['item'=>__('general.sensor'),'name'=>'\''.$sensor->name.'\'']).'")']) !!}
		            {!! Form::button('<i class="fa fa-trash-o"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right']) !!}
		        	{!! Form::close() !!}
		        	@endpermission
				</td>
			</tr>
			@endforeach
			</table>
			{!! $sensors->render() !!}
		@endslot
	@endcomponent
@endsection