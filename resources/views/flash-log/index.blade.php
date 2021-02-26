@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.FlashLog')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.FlashLog')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('flash-log.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.FlashLog')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-flash-log").DataTable(
                    {
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 0, "desc" ]
                    ],
                });
            });
        </script>


        <table id="table-flash-log" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>User</th><th>Device</th><th>Hive</th><th>Log Messages</th><th>Log Saved</th><th>Log Parsed</th><th>Log Has Timestamps</th><th>Bytes Received</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($flashlog as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ isset($item->user) ? $item->user->name : '' }}</td>
                    <td>{{ isset($item->device) ? $item->device->name.' ('.$item->device->id.')' : '' }}</td>
                    <td>{{ isset($item->hive) ? $item->hive->name : '' }}</td>
                    <td>{{ $item->log_messages }}</td>
                    <td>{{ $item->log_saved }}</td>
                    <td>{{ $item->log_parsed }}</td>
                    <td>{{ $item->log_has_timestamps }}</td>
                    <td>{{ $item->bytes_received }}</td>
                    <td col-sm-1>
                        <a href="{{ route('flash-log.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('flash-log.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('flash-log.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'FlashLog','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $flashlog->render() !!} </div>

        @endslot
    @endcomponent
@endsection
