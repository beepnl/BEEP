@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.DashboardGroup')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.DashboardGroup')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('dashboard-group.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.DashboardGroup')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-dashboard-group").DataTable(
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


        <table id="table-dashboard-group" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Id</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Hive Ids</th>
                    <th>Speed</th>
                    <th>Interval</th>
                    <th>Show Inspections</th>
                    <th>Show All</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($dashboardgroup as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->user_id }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ implode(', ', $item->hives()->pluck('name')->toArray()) }}</td>
                    <td>{{ $item->speed }}</td>
                    <td>{{ \App\Models\DashboardGroup::$intervals[$item->interval] }}</td>
                    <td>{{ $item->show_inspections }}</td>
                    <td>{{ $item->show_all }}</td>
                    <td col-sm-1>
                        <a href="{{ route('dashboard-group.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('dashboard-group.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('dashboard-group.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'DashboardGroup','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $dashboardgroup->render() !!} </div>

        @endslot
    @endcomponent
@endsection
