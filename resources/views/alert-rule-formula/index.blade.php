@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.AlertRuleFormula')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.AlertRuleFormula')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('alert-rule-formula.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.AlertRuleFormula')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-alert-rule-formula").DataTable(
                    {
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 1, "asc" ]
                    ],
                });
            });
        </script>


        <table id="table-alert-rule-formula" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Alert Rule Id</th><th>Measurement Id</th><th>Calculation</th><th>Comparator</th><th>Comparison</th><th>Logical</th><th>Period Minutes</th><th>Threshold Value</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($alertruleformula as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->alert_rule_id }}</td><td>{{ $item->measurement_id }}</td><td>{{ $item->calculation }}</td><td>{{ $item->comparator }}</td><td>{{ $item->comparison }}</td><td>{{ $item->logical }}</td><td>{{ $item->period_minutes }}</td><td>{{ $item->threshold_value }}</td>
                    <td col-sm-1>
                        <a href="{{ route('alert-rule-formula.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('alert-rule-formula.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('alert-rule-formula.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'AlertRuleFormula','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $alertruleformula->render() !!} </div>

        @endslot
    @endcomponent
@endsection
