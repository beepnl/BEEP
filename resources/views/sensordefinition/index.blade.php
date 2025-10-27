@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.SensorDefinition')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.SensorDefinition')]) }}
            
            {!! Form::open(['method' => 'GET', 'route' => 'sensordefinition.index', 'class' => 'form-inline', 'role' => 'search'])  !!}
                
                <div class="input-group" style="display: inline-block;">
                    {!! Form::select('measurement_id', App\Measurement::selectList(), e($search_mid ?? null), array('style'=>'max-width: 300px; font-size:10px;', 'onchange'=>'this.form.submit()', 'placeholder'=>__('crud.select', ['item'=>__('beep.measurement')]),'class' => 'form-control select2')) !!}
                </div>
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
                    <input type="text" class="form-control" style="max-width: 100px;" name="search" placeholder="Sensor def prop..." value="{{ request('search') }}">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
                    </span>
                </div>
                {!! Form::hidden('page', $page) !!}
            
            {!! Form::close() !!}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('sensordefinition.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.SensorDefinition')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-sensordefinition").DataTable(
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


        <table id="table-sensordefinition" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Created / Updated</th>
                    <th>Name</th>
                    <th>Inside</th>
                    <th>Zero Value</th>
                    <th>Unit Per Value</th>
                    <th>Measurement in</th>
                    <th>Measurement out</th>
                    <th>Recalculate</th>
                    <th>Device</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($sensordefinition as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->created_at }}<br>{{ $item->updated_at }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->inside }}</td>
                    <td>{{ $item->offset }}</td>
                    <td>{{ $item->multiplier }}</td>
                    <td>{{ isset($item->input_measurement) ? $item->input_measurement->abbreviation : '-' }}</td>
                    <td>{{ isset($item->output_measurement) ? $item->output_measurement->abbreviation : '-'  }}</td>
                    <td>{{ $item->recalculate }}</td>
                    <td>{{ isset($item->device) ? $item->device->name : '-'  }}</td>
                    <td col-sm-1>
                        <a href="{{ route('sensordefinition.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('sensordefinition.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('sensordefinition.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'sensordefinition','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $sensordefinition->appends(Request::except('page'))->render() !!} </div>

        @endslot
    @endcomponent
@endsection
