@extends('layouts.app')

@section('page-title') {{ __('beep.Image').': '.(isset($image->name) ? $image->name : __('general.Item')).' ('.$image->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($image->name) ? $image->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('image.edit', $image->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $image->id }}</td>
                    </tr>
                    <tr><th> File </th><td> {{ $image->file }} </td></tr><tr><th> Description </th><td> {{ $image->description }} </td></tr><tr><th> Type </th><td> {{ $image->type }} </td></tr><tr><th> Height </th><td> {{ $image->height }} </td></tr><tr><th> Width </th><td> {{ $image->width }} </td></tr><tr><th> Size Kb </th><td> {{ $image->size_kb }} </td></tr><tr><th> Date </th><td> {{ $image->date }} </td></tr><tr><th> User Id </th><td> {{ $image->user_id }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
