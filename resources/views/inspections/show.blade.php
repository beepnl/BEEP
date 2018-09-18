@extends('layouts.app')

@section('page-title') {{ __('beep.Inspection').': '.(isset($inspection->name) ? $inspection->name : __('crud.Item')).' ('.$inspection->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($inspection->name) ? $inspection->name : __('crud.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('inspections.edit', $inspection->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')

            @if($inspection->items()->count() > 0)
            <div class="col-xs-12">
                <p><strong>Inspection items:</strong></p>
            </div>
            <table class="table table-responsive table-striped">
                <thead>
                    <tr>
                        <th class="col-md-4" style="text-align: right;">Category</th>
                        <th>Value</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td style="text-align: right;">
                            <span style="font-size: 10px;">{{$item->ancestors()}}</span>
                            <span> <strong>{{$item->name()}}</strong></span>
                        </td>
                        <td>{{$item->val()}}</td>
                        <td>{{$item->unit()}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @else
            -
            @endif

            <hr>
            <br>
            <div class="col-xs-12">
                <p><strong>Inspection specifications:</strong></p>
            </div>
            <table class="table table-responsive table-striped">
                <tbody>
                    <tr><th class="col-md-4" style="text-align: right;">ID</th><td>{{ $inspection->id }}</td></tr>
                    <tr><th style="text-align: right;"> Notes </th><td> {{ $inspection->notes }} </td></tr>
                    <tr><th style="text-align: right;"> Impression </th><td> {{ $inspection->impression }} </td style="text-align: right;"></tr>
                    <tr><th style="text-align: right;"> Attention </th><td> {{ $inspection->attention }} </td></tr>
                    <tr><th style="text-align: right;"> Created At </th><td> {{ $inspection->created_at }} </td></tr>
                </tbody>
            </table>

        @endslot
    @endcomponent
@endsection
