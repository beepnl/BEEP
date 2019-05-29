@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.Inspection')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.Inspection')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('inspections.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.Inspection')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-inspections").DataTable(
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


        <table id="table-inspections" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Users</th>
                    <th>Hives</th>
                    <th>Locations</th>
                    <th>Items</th>
                    <th>Notes</th>
                    <th>Impression</th>
                    <th>Attention</th>
                    <th>Created At</th>
                    <th class="col-xs-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($inspections as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->users()->pluck('name')->implode(', ') }}</td>
                    <td>{{ $item->hives()->pluck('name')->implode(', ') }} ({{ $item->hives()->pluck('id')->implode(', ') }})</td>
                    <td>{{ $item->locations()->pluck('name')->implode(', ') }}</td>
                    <td>{{ $item->items()->count() }}</td>
                    <td>{{ $item->notes }}</td><td>{{ $item->impression }}</td><td>{{ $item->attention }}</td><td>{{ $item->created_at }}</td>
                    <td col-sm-1>
                        <a href="{{ route('inspections.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('inspections.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('inspections.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'Inspection','name'=>'']) }}')">
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
