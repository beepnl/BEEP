@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.SampleCode')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.SampleCode')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('sample-code.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.SampleCode')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-sample-code").DataTable(
                    {
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 3, "desc" ]
                    ],
                });
            });
        </script>


        <table id="table-sample-code" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Sample Code</th><th>Sample Note</th><th>Sample Date</th><th>Test Result</th><th>Test</th><th>Test Date</th><th>Test Lab Name</th><th>Hive Id</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($samplecode as $item)
                <tr>
                    <td>{{ $loop->iteration ?? $item->id }}</td>
                    <td>{{ $item->sample_code }}</td><td>{{ $item->sample_note }}</td><td>{{ $item->sample_date }}</td><td>{{ $item->test_result }}</td><td>{{ $item->test }}</td><td>{{ $item->test_date }}</td><td>{{ $item->test_lab_name }}</td><td>{{ $item->hive_id }}</td>
                    <td col-sm-1>
                        <a href="{{ route('sample-code.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('sample-code.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('sample-code.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'SampleCode','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @endslot
    @endcomponent
@endsection
