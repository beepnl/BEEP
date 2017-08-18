@extends('layouts.app')
 
@section('page-title') {{ __('crud.management', ['item'=>__('general.group')]) }}
@endsection

@section('content')

			
	@component('components/box')
		@slot('title')
			{{ __('crud.overview', ['item'=>__('general.groups')]) }}
		@endslot

		@slot('action')
			@permission('group-create')
	            <a class="btn btn-primary" href="{{ route('groups.create') }}"><i class="fa fa-plus"></i> {{ __('crud.add', ['item'=>__('general.group')]) }}</a>
	            @endpermission
		@endslot

		@slot('body')
			<table class="table table-striped">
				<tr>
					<th>{{ __('crud.id') }}</th>
					<th>{{ __('crud.name') }}</th>
					<th>{{ __('crud.type') }}</th>
					<th>{{ __('crud.actions') }}</th>
				</tr>
			@foreach ($groups as $key => $group)
			<tr>
				<td>{{ $group->id }}</td>
				<td>{{ $group->name }}</td>
				<td>{{ $group->type }}</td>
				<td>
					<a class="btn btn-default" href="{{ route('groups.show',$group->id) }}" title="{{ __('crud.show') }}"><i class="fa fa-eye"></i></a>
					@permission('group-edit')
					<a class="btn btn-primary" href="{{ route('groups.edit',$group->id) }}" title="{{ __('crud.edit') }}"><i class="fa fa-pencil"></i></a>
					@endpermission
					@permission('group-delete')
					{!! Form::open(['method' => 'DELETE','route' => ['groups.destroy', $group->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("'.__('crud.sure',['item'=>__('general.group'),'name'=>'\''.$group->name.'\'']).'")']) !!}
		            {!! Form::button('<i class="fa fa-trash-o"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right']) !!}
		        	{!! Form::close() !!}
		        	@endpermission
				</td>
			</tr>
			@endforeach
			</table>
			{!! $groups->render() !!}
		@endslot
	@endcomponent
@endsection