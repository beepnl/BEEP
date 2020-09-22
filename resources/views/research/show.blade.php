@extends('layouts.app')

@section('page-title') {{ __('beep.Research').': '.(isset($research->name) ? $research->name : __('general.Item')).' ('.$research->id.')' }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ (isset($research->name) ? $research->name : __('general.Item')).' '.__('crud.attributes') }}
        @endslot

        @slot('action')
            @permission('role-edit')
                <a href="{{ route('research.edit', $research->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
            @endpermission
        @endslot

        @slot('body')
            <!-- <h1>Research</h1>
            <table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $research->id }}</td>
                    </tr>
                    <tr>
                        <th> Name </th><td> {{ $research->name }} </td></tr><tr>
                        <th> Image </th><td> <img src="{{ $research->image->image_url }}" style="width: 100px;"> </td></tr><tr>
                        <th> Description </th><td> {{ $research->description }} </td></tr><tr>
                        <th> Type </th><td> {{ $research->type }} </td></tr><tr>
                        <th> Institution </th><td> {{ $research->institution }} </td></tr><tr>
                        <th> Type Of Data Used </th><td> {{ $research->type_of_data_used }} </td></tr><tr>
                        <th> Start Date </th><td> {{ $research->start_date }} </td></tr><tr>
                        <th> End Date </th><td> {{ $research->end_date }} </td></tr>
                </tbody>
            </table> -->

            <h1>Research consent data</h1>
            <div class="col-xs-12">
                <form method="GET" action="{{ route('research.show',$research->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                    
                    <div class="col-xs-7">
                        <div class="form-group {{ $errors->has('user_ids') ? 'has-error' : ''}}">
                            <label for="user_ids" control-label>{{ 'Select consented users' }}</label>
                            <div>
                                {!! Form::select('user_ids[]', $consent_users_select, $consent_users_selected, array('id'=>'user_ids','class' => 'form-control select2', 'multiple')) !!}
                                {!! $errors->first('user_ids', '<p class="help-block">:message</p>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-1">
                    </div>
                    <div class="col-xs-4">
                        <div class="form-group">
                            <label control-label>Load data</label>
                            <a href="{{ route('research.show', $research->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-primary btn-block"><i class="fa fa-refresh" aria-hidden="true"></i> Reload consent data table</button></a>
                        </div>
                    </div>
                </form>
            </div>
            <div style="display: inline-block;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            <th class="rotate"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-user"></i> Users</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-map-marker"></i> Apiaries</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-archive"></i> Hives</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-edit"></i> Inspections</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-feed"></i> Devices</span></th> 
                        </tr>
                        <tr>
                            <th class="row-header"><span><i class="fa fa-2x fa-line-chart"></i> Measurements</span></th> 
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="display: inline-block; width: calc( 100% - 203px); overflow-y: hidden; overflow-x: auto;">
                <table class="table table-responsive table-striped table-header-rotated">
                    <thead>
                        <tr>
                            @foreach($dates as $date => $d)
                                <th class="rotate"><div><span>{{ $date }}</span></div></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['users'] > 0 ? $d['users'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['apiaries'] > 0 ? $d['apiaries'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['hives'] > 0 ? $d['hives'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['inspections'] > 0 ? $d['inspections'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['devices'] > 0 ? $d['devices'] : '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($dates as $date => $d)
                                <td>{{ $d['measurements'] > 0 ? $d['measurements'] : '' }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

        @endslot
    @endcomponent
@endsection
