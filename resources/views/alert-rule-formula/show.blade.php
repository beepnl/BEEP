@extends('layouts.app')

@section('page-title') {{ __('beep.AlertRuleFormula').': '.(isset($alertruleformula->name) ? $alertruleformula->name : __('general.Item')).' ('.$alertruleformula->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($alertruleformula->name) ? $alertruleformula->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('alert-rule-formula.edit', $alertruleformula->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th><td>{{ $alertruleformula->id }}</td>
                    </tr>
                    <tr><th> Alert Rule Id </th><td> {{ $alertruleformula->alert_rule_id }} </td></tr><tr><th> Measurement Id </th><td> {{ $alertruleformula->measurement_id }} </td></tr><tr><th> Calculation </th><td> {{ $alertruleformula->calculation }} </td></tr><tr><th> Comparator </th><td> {{ $alertruleformula->comparator }} </td></tr><tr><th> Comparison </th><td> {{ $alertruleformula->comparison }} </td></tr><tr><th> Logical </th><td> {{ $alertruleformula->logical }} </td></tr><tr><th> Period Minutes </th><td> {{ $alertruleformula->period_minutes }} </td></tr><tr><th> Threshold Value </th><td> {{ $alertruleformula->threshold_value }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
