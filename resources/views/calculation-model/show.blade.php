@extends('layouts.app')

@section('page-title') {{ __('beep.CalculationModel').': '.(isset($calculationmodel->name) ? $calculationmodel->name : __('general.Item')).' ('.$calculationmodel->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($calculationmodel->name) ? $calculationmodel->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('calculation-model.edit', $calculationmodel->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
                <a href="{{ route('calculation-model.run', $calculationmodel->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-danger loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
        @endslot

        @slot('body')

            @if(isset($model_result))

                <textarea rows="32" style="width: 100%;">
                    {{ json_encode($model_result, JSON_PRETTY_PRINT) }}
                </textarea>

            @else

                <table class="table table-responsive table-striped">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <td>{{ $calculationmodel->id }}</td>
                        </tr>
                        <tr>
                            <th> Name </th>
                            <td> {{ $calculationmodel->name }} </td>
                        </tr>
                        <tr>
                            <th> Measurement Id </th>
                            <td> {{ $calculationmodel->measurement_id }} </td>
                        </tr>
                        <tr>
                            <th> Data Measurement Id </th>
                            <td> {{ $calculationmodel->data_measurement_id }} </td>
                        </tr>
                        <tr>
                            <th> Data Interval </th>
                            <td> {{ $calculationmodel->data_interval }} </td>
                        </tr>
                        <tr>
                            <th> Data Relative Interval </th>
                            <td> {{ $calculationmodel->data_relative_interval }} </td>
                        </tr>
                        <tr>
                            <th> Data Interval Index </th>
                            <td> {{ $calculationmodel->data_interval_index }} </td>
                        </tr>
                        <tr>
                            <th> Data Api Url </th>
                            <td> {{ $calculationmodel->data_api_url }} </td>
                        </tr>
                        <tr>
                            <th> Data Api Http Request </th>
                            <td> {{ $calculationmodel->data_api_http_request }} </td>
                        </tr>
                    </tbody>
                </table>

            @endif

        @endslot
    @endcomponent
@endsection
