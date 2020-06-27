@extends('layouts.app')

@section('page-title') {{ __('beep.SampleCode').': '.(isset($samplecode->name) ? $samplecode->name : __('general.Item')).' ('.$samplecode->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($samplecode->name) ? $samplecode->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('sample-code.edit', $samplecode->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $samplecode->id }}</td>
                    </tr>
                    <tr><th> Sample Code </th><td> {{ $samplecode->sample_code }} </td></tr><tr><th> Sample Note </th><td> {{ $samplecode->sample_note }} </td></tr><tr><th> Sample Date </th><td> {{ $samplecode->sample_date }} </td></tr><tr><th> Test Result </th><td> {{ $samplecode->test_result }} </td></tr><tr><th> Test </th><td> {{ $samplecode->test }} </td></tr><tr><th> Test Date </th><td> {{ $samplecode->test_date }} </td></tr><tr><th> Test Lab Name </th><td> {{ $samplecode->test_lab_name }} </td></tr><tr><th> Hive Id </th><td> {{ $samplecode->hive_id }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
