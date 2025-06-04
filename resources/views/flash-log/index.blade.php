@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.FlashLog')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.Flash_logs')]) }}
            {!! Form::open(['method' => 'GET', 'route' => 'flash-log.index', 'class' => 'form-inline', 'role' => 'search'])  !!}
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 100px;" name="device" placeholder="Device..." value="{{ request('device') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="text" class="form-control" style="max-width: 100px;" name="user" placeholder="User..." value="{{ request('user') }}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                </span>
            </div>
            <div class="input-group" style="display: inline-block;">
                <input type="number" class="form-control" style="max-width: 100px;" step="0.1" placeholder="MB >" name="mb" value="{{ request('mb') }}">
            </div>

            <div class="input-group" style="display: inline-block; margin-left: 20px;">
                <label for="log_parsed" control-label>{{ 'Log parsed' }}</label>
                <div>
                    <div class="radio">
                        <label><input onchange="this.form.submit()" name="log_parsed" type="radio" value="1" {{ ('' !== request('log_parsed') && '1' == request('log_parsed')) ? 'checked' : '' }}> Yes</label>
                    </div>
                    <div class="radio">
                        <label><input onchange="this.form.submit()" name="log_parsed" type="radio" value="0" @if ('' !== request('log_parsed')) {{ ('0' == request('log_parsed')) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
                    </div>
                </div>
            </div>
            <div class="input-group" style="display: inline-block; margin-left: 20px;">
                <label for="log_has_timestamps" control-label>{{ 'Has time' }}</label>
                <div>
                    <div class="radio">
                        <label><input onchange="this.form.submit()" name="log_has_timestamps" type="radio" value="1" {{ ('' !== request('log_has_timestamps') && '1' == request('log_has_timestamps')) ? 'checked' : '' }}> Yes</label>
                    </div>
                    <div class="radio">
                        <label><input onchange="this.form.submit()" name="log_has_timestamps" type="radio" value="0" @if ('' !== request('log_has_timestamps')) {{ ('0' == request('log_has_timestamps')) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
                    </div>
                </div>
            </div>
            <div class="input-group" style="display: inline-block; margin-left: 20px;">
                <label for="csv_url" control-label>{{ 'Has CSV' }}</label>
                <div>
                    <div class="radio">
                        <label><input onchange="this.form.submit()" name="csv_url" type="radio" value="1" {{ ('' !== request('csv_url') && '1' == request('csv_url')) ? 'checked' : '' }}> Yes</label>
                    </div>
                    <div class="radio">
                        <label><input onchange="this.form.submit()" name="csv_url" type="radio" value="0" @if ('' !== request('csv_url')) {{ ('0' == request('csv_url')) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
            <span><h5><em>NB: De 'Device', 'User' & 'Flashlog properties' filter velden filteren max 50 flashlog op volgorde van de laatste upload uit de database</em></h5></span>
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
                    "pageLength": 50,
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

        <div style="overflow-x: scroll;">
        <table id="table-flash-log" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="min-width: 60px;">Upload date</th>
                    <th style="min-width: 60px;">Last update</th>
                    <th>User</th>
                    <th style="min-width: 100px;">Device</th>
                    <th>Hive</th>
                    <th>Messages</th>
                    <th>Time %</th>
                    <th>Erased/ Parsed/ Time</th>
                    <th style="min-width: 60px;">Log start</th>
                    <th style="min-width: 60px;">Log end</th>
                    <th style="min-width: 60px;">Time log %</th>
                    <th>Size</th>
                    <th>Persisted</th>
                    <th style="min-width: 280px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($flashlog as $item)
                <tr @if($item->validLog()) style="background: lightgreen;" @endif>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->updated_at }}</td>
                    <td>{{ $item->user_name }}</td>
                    <td>{{ $item->device_name }}</td>
                    <td>{{ $item->hive_name }}</td>
                    <td>{{ $item->log_messages }}</td>
                    <td>{{ $item->time_percentage }}%</td>
                    <td>{{ $item->log_erased }} / {{ $item->log_parsed }} / {{ $item->log_has_timestamps }}</td>
                    <td>{{ $item->log_date_start }}</td>
                    <td>{{ $item->log_date_end }}</td>
                    <td>{{ $item->getTimeLogPercentage() }}% of<br>{{ $item->getLogDays() }} days</td>
                    <td>{{ round($item->bytes_received/1024/1024,3) }}MB @if(isset($item->log_size_bytes) && $item->log_size_bytes > 0) ({{ round(100*($item->bytes_received / $item->log_size_bytes),1) }}%) @endif </td>
                    <td>@if(isset($item->persisted_block_ids))
                        Days: {{ $item->persisted_days }}, 
                        Blok: {{ $item->persisted_block_ids }}<br>
                        Meas: {{ $item->persisted_measurements }} (@if($item->log_messages > 0){{ round(100 * $item->persisted_measurements / $item->log_messages) }}%@else 0%@endif), 
                        @endif
                    </td>
                    <td col-sm-1>
                        <a href="{{ route('flash-log.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('flash-log.parse', array_merge(request()->query(), ['id'=>$item->id]) ) }}" title="{{ __('crud.parse') }}"><button title="Parse Flashlog with time fill and adding Sensordefinitions (slow)" class="btn btn-info loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
                        
                        {{-- <a href="{{ route('flash-log.parse', array_merge(request()->query(), ['id'=>$item->id, 'no_sensor_def'=>1]) ) }}" title="{{ __('crud.parse') }}"><button title="Parse Flashlog with time fill, but without adding Sensordefinitions" class="btn btn-default loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a> --}}

                        <a href="{{ route('flash-log.parse', array_merge(request()->query(), ['id'=>$item->id, 'add_meta'=>1]) ) }}" title="{{ __('crud.parse') }}"><button title="Add meta data to Flashlog" class="btn btn-warning loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>
                        
                        <a href="{{ route('flash-log.parse', array_merge(request()->query(), ['id'=>$item->id, 'csv'=>1]) ) }}" title="{{ __('crud.parse') }}"><button title="Create new CSV" class="btn btn-success loading-spinner" data-loading-text="<i class='fa fa-refresh fa-spin'></i>"><i class="fa fa-table" aria-hidden="true"></i></button></a>

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
        </div>

        <div class="pagination-wrapper"> {!! $flashlog->render() !!} </div>

        @endslot
    @endcomponent
@endsection
