@extends('layouts.app')

@section('page-title') {{ __('beep.HiveTag').': '.(isset($hivetag->name) ? $hivetag->name : __('general.Item')).' ('.$hivetag->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($hivetag->name) ? $hivetag->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('hive-tags.edit', $hivetag->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $hivetag->id }}</td>
                    </tr>
                    <tr><th> User Id </th><td> {{ $hivetag->user_id }} </td></tr><tr><th> Tag </th><td> {{ $hivetag->tag }} </td></tr><tr><th> Hive Id </th><td> {{ $hivetag->hive_id }} </td></tr><tr><th> Action Id </th><td> {{ $hivetag->action_id }} </td></tr><tr><th> Router Link </th><td> {{ $hivetag->router_link }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
