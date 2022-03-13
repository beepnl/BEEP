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
                    <th>#</th>
                    <th>Upload date</th>
                    <th>Last update</th>
                    <th>User</th>
                    <th>Device</th>
                    <th>Hive</th>
                    <th>Messages</th>
                    <th>Time %</th>
                    <th>Log erased</th>
                    <th>Log parsed</th>
                    <th>Log time</th>
                    <th>Log size</th>
                    <th style="width: 190px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($flashlog as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->updated_at }}</td>
                    <td>{{ $item->user_name }}</td>
                    <td>{{ $item->device_name }}</td>
                    <td>{{ $item->hive_name }}</td>
                    <td>{{ $item->log_messages }}</td>
                    <td>{{ $item->time_percentage }}%</td>
                    <td>{{ $item->log_erased }}</td>
                    <td>{{ $item->log_parsed }}</td>
                    <td>{{ $item->log_has_timestamps }}</td>
                    <td>{{ round($item->bytes_received/1024/1024,3) }}MB @if(isset($item->log_size_bytes) && $item->log_size_bytes > 0) ({{ round(100*($item->bytes_received / $item->log_size_bytes),1) }}%) @endif </td>
                    <td col-sm-1>
                        <a href="{{ route('flash-log.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('flash-log.parse', $item->id) }}" title="{{ __('crud.parse') }}"><button title="Parse Flashlog with time fill and adding Sensordefinitions (slow)" class="btn btn-info loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
                        
                        <a href="{{ route('flash-log.parse', ['id'=>$item->id, 'no_sensor_def'=>1] ) }}" title="{{ __('crud.parse') }}"><button title="Parse Flashlog with time fill, but without adding Sensordefinitions" class="btn btn-default loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>

                        <a href="{{ route('flash-log.parse', ['id'=>$item->id, 'no_fill'=>1] ) }}" title="{{ __('crud.parse') }}"><button title="Parse Flashlog without time fill and without adding Sensordefinitions " class="btn btn-warning loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
                        
                        

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
