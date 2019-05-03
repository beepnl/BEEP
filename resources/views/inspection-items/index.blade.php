@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.InspectionItem')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.InspectionItem')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('inspection-items.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.InspectionItem')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-inspection-items").DataTable(
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


        <table id="table-inspection-items" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Value</th><th>Inspection Id</th><th>Category Id</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($inspectionitems as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->value }}</td><td>{{ $item->inspection_id }}</td><td>{{ $item->category_id }}</td>
                    <td col-sm-1>
                        <a href="{{ route('inspection-items.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('inspection-items.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('inspection-items.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'InspectionItem','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $inspectionitems->render() !!} </div>

        @endslot
    @endcomponent
@endsection
