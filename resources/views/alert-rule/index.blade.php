@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.AlertRule')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.AlertRule')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('alert-rule.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.AlertRule')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-alert-rule").DataTable(
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


        <table id="table-alert-rule" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Description</th><th>Measurement</th><th>Calculation</th><th>Calculation Minutes</th><th>Comparator</th><th>Comparison</th><th>Threshold Value</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($alertrule as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->name }}</td><td>{{ $item->description }}</td><td>{{ $item->measurement->pq_name_unit }}</td><td>{{ $item->calculation }}</td><td>{{ $item->calculation_minutes }}</td><td>{{ $item->comparator }}</td><td>{{ $item->comparison }}</td><td>{{ $item->threshold_value }}</td>
                    <td col-sm-1>
                        <a href="{{ route('alert-rule.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('alert-rule.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('alert-rule.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'AlertRule','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $alertrule->render() !!} </div>

        @endslot
    @endcomponent
@endsection
