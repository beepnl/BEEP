@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.AlertRule')]) }}
    {!! Form::open(['method' => 'GET', 'route' => 'alert-rule.index', 'class' => 'form-inline', 'role' => 'search', 'style'=>'display: inline-block;'])  !!}
    <div class="input-group" style="display: inline-block;">
        <input type="text" class="form-control" style="max-width: 100px;" name="rule_id" placeholder="Rule ID" value="{{ $rule_id }}">
        <span class="input-group-btn">
            <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
        </span>
    </div>
    {{-- <div class="input-group" style="display: inline-block;">
        <input type="text" class="form-control" style="max-width: 100px;" name="user_id" placeholder="User ID" value="{{ $user_id }}">
        <span class="input-group-btn">
            <button type="submit" class="btn btn-deafult"><i class="fa fa-search"></i></button>
        </span>
    </div> --}}
    <div class="input-group" style="display: inline-block; font-size:16px;">
        {!! Form::select('user_id', $users, $user_id, array('class' => 'form-control select2', 'placeholder'=>'Select user...', 'onchange'=>'this.form.submit()')) !!}
        {!! $errors->first('user_id', '<p class="help-block">:message</p>') !!}
    </div>
    <div style="display: inline-block;">
        <input type="checkbox" style="min-width: 10px;" name="no_measurements" value="1" onchange="this.form.submit();" @if($no_meas) checked @endif> <span style="font-size:16px;">No measurements</span></input>
    </div>
    <div style="display: inline-block;">
        <input type="checkbox" style="min-width: 10px;" name="default_rule" value="1" onchange="this.form.submit();" @if($default_rule) checked @endif> <span style="font-size:16px;">Default</span></input>
    </div>
    {!! Form::close() !!}
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

        @slot('bodyClass') table-responsive
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
                        [ 3, "desc" ],
                        [ 5, "desc" ]
                    ],
                });
            });
        </script>

        <div class="row">
        <table id="table-alert-rule" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Name</th>
                    <th>Active</th>
                    <th>Minutes</th>
                    <th>Last evaluated</th>
                    <th>Last alert</th>
                    <th>Function</th>
                    <th>Measurement</th>
                    <th>Alerts</th>
                    <th>Email</th>
                    <th>Default</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($alertrule as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ isset($item->user_id) ? $item->user->name.' ('.$item->user_id.')' : '' }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->active }}</td>
                    <td>{{ $item->calculation_minutes }} x{{ $item->alert_on_occurences }}</td>
                    <td>{{ $item->last_evaluated_at }}</td>
                    <td>{{ $item->last_calculated_at }}</td>
                    <td>
                        @if($item->formulas->count() > 0)
                        <div>Formulas: 
                            @foreach($item->formulas as $f)
                            <a href="/alert-rule-formula/{{$f->id}}"><span class="badge badge-default">{{$f->id}}: {{$f->readableFunction(true)}}</span></a>
                            @endforeach
                        </div>
                        @else
                        {{ $item->readableFunction() }}
                        @endif
                    </td>
                    <td>{{ $item->measurement->pq_name_unit }}</td>
                    <td>{{ $item->alerts()->count() }}</td>
                    <td>{{ $item->alert_via_email }}</td>
                    <td>{{ $item->default_rule }}</td>
                    <td style="min-width: 210px;">
                        <a href="{{ route('alert.index', ['rule_id'=>$item->id]) }}" title="{{ __('crud.show') }} Alerts"><button class="btn btn-default"><i class="fa fa-bell" aria-hidden="true"></i></button></a>
                        <a href="{{ route('alert-rule.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
                        <a href="{{ route('alert-rule.parse', $item->id) }}" title="Run AlertRule now"><button class="btn btn-warning"><i class="fa fa-refresh" aria-hidden="true"></i></button></a>

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
        </div>

        <div class="pagination-wrapper"> {!! $alertrule->render() !!} </div>
        @endslot
    @endcomponent
@endsection
