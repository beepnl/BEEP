@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('general.role')]) }}
@endsection

@section('content')

			
	@component('components/box')
		@slot('title')
			{{ __('crud.overview', ['item'=>__('general.roles')]) }}
		@endslot
		@slot('action')
			@permission('role-create')
	            <a class="btn btn-primary" href="{{ route('roles.create') }}"><i class="fa fa-plus"></i> {{ __('crud.add', ['item'=>__('general.role')]) }}</a>
	        @endpermission
		@endslot

		@slot('body')
		<table class="table table-striped">
			<tr>
				<th>{{ __('crud.id') }}</th>
				<th>{{ __('crud.name') }}</th>
				<th>{{ __('crud.display_name') }}</th>
				<th>{{ __('crud.description') }}</th>
				<th>{{ __('crud.actions') }}</th>
			</tr>
		@foreach ($roles as $key => $role)
		<tr>
			<td>{{ $role->id }}</td>
			<td>{{ $role->name }}</td>
			<td>{{ $role->display_name }}</td>
			<td>{{ $role->description }}</td>
			<td>
				<a class="btn btn-default" href="{{ route('roles.show',$role->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a>
					@permission('role-edit')
					<a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}" title="{{ __('crud.edit') }}"><i class="fa fa-pencil"></i></a>
					@endpermission
					@permission('role-delete')
					{!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("'.__('crud.sure',['item'=>__('general.role'),'name'=>'\''.$role->display_name.'\'']).'")']) !!}
		            {!! Form::button('<i class="fa fa-trash-o"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right']) !!}
		        	{!! Form::close() !!}
		        	@endpermission
			</td>
		</tr>
		@endforeach
		</table>
		{!! $roles->render() !!}
		@endslot

	@endcomponent
@endsection