@extends('layouts.app')

@section('page-title') {{ __('beep.language').': '.(isset($language->name) ? $language->name : __('general.Item')).' ('.$language->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($language->name) ? $language->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('languages.edit', $language->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $language->id }}</td>
                    </tr>
                    <tr><th> Name </th><td> {{ $language->name }} </td></tr><tr><th> Name English </th><td> {{ $language->name_english }} </td></tr><tr><th> Icon </th><td> {{ $language->icon }} </td></tr><tr><th> Abbreviation </th><td> {{ $language->abbreviation }} </td></tr><tr><th> Abbreviation two characters</th><td> {{ $language->twochar }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
