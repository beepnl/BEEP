@extends('layouts.app')

@section('page-title') {{ __('beep.measurement').': '.(isset($measurement->name) ? $measurement->name : __('general.Item')).' ('.$measurement->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($measurement->name) ? $measurement->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('measurement.edit', $measurement->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $measurement->id }}</td>
                    </tr>
                    <tr><th> Abbreviation </th><td> {{ $measurement->abbreviation }} </td></tr>
                    <tr><th> Physical Quantity</th><td> {{ $measurement->pq_name_unit() }} </td></tr>
                    <tr><th> Show In Charts </th><td> {{ $measurement->show_in_charts }} </td></tr>
                    <tr><th> Chart Group </th><td> {{ $measurement->chart_group }} </td></tr>
                    <tr><th> Min value </th><td> {{ $measurement->min_value }} </td></tr>
                    <tr><th> Max value </th><td> {{ $measurement->max_value }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
