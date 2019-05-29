@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.checklist')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.checklist')]) }}
        @endslot

        @slot('action')
                {{-- @role('superadmin')
                    <form method="POST" action="{{ route('checklists.copies') }}" accept-charset="UTF-8" style="display:inline">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'All _copy checklists','name'=>'']) }}')">
                            <i class="fa fa-trash-o" aria-hidden="true"></i> Delete all type '_copy' checklists 
                        </button>
                    </form>
                @endrole --}}

                <a href="{{ route('checklists.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.checklist')]) }}
                </a>
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-checklists").DataTable(
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


        <table id="table-checklists" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Type</th><th>Description</th>
                    <th># Categories</th>
                    <th>Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($checklists as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->name }}</td><td>{{ $item->type }}</td><td>{{ $item->description }}</td>
                    <td>{{ $item->categories()->count() }}</td>
                    <td>{{ $item->users->pluck('name')->implode(', ') }}</td>
                    <td col-sm-1>
                        <a href="{{ route('checklists.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('checklists.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('checklists.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'checklist','name'=>'']) }}')">
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
