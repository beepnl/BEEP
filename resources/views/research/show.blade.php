@extends('layouts.app')

@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' ('.$research->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($research->name) ? $research->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('research.edit', $research->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $research->id }}</td>
                    </tr>
                    <tr><th> Name </th><td> {{ $research->name }} </td></tr><tr><th> Image </th><td> {{ $research->image }} </td></tr><tr><th> Description </th><td> {{ $research->description }} </td></tr><tr><th> Type </th><td> {{ $research->type }} </td></tr><tr><th> Institution </th><td> {{ $research->institution }} </td></tr><tr><th> Type Of Data Used </th><td> {{ $research->type_of_data_used }} </td></tr><tr><th> Start Date </th><td> {{ $research->start_date }} </td></tr><tr><th> End Date </th><td> {{ $research->end_date }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
