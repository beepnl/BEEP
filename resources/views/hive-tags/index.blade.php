@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.HiveTag')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.HiveTag')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('hive-tags.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.HiveTag')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-hive-tags").DataTable(
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


        <table id="table-hive-tags" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>User Id</th><th>Tag</th><th>Hive Id</th><th>Action frontend</th><th>Router Link</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($hivetags as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->user_id }}</td><td>{{ $item->tag }}</td><td>{{ $item->hive_id }}</td><td>{{ $item->action_id }}</td><td>{{ $item->router_link }}</td>
                    <td col-sm-1>
                        <a href="{{ route('hive-tags.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('hive-tags.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('hive-tags.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'HiveTag','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $hivetags->render() !!} </div>

        @endslot
    @endcomponent
@endsection
