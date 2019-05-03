@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>'categoryinput']) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.categoryinput')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('categoryinputs.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.categoryinput')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-categoryinputs").DataTable(
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

        <div class="row">
            
            <div class="col-md-12">

                <table id="table-categoryinputs" class="table table-responsive table-striped">
                    <thead>
                        <tr>
                            <th>#</th><th>Name</th><th>Type</th><th>Min</th><th>Max</th><th>Decimals</th><th>Icon</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($categoryinputs as $item)
                        <tr>
                            <td>{{ $loop->iteration or $item->id }}</td>
                            <td>{{ $item->name }}</td><td>{{ $item->type }}</td><td>{{ $item->min }}</td><td>{{ $item->max }}</td><td>{{ $item->decimals }}</td><td>{{ $item->icon }}</td>
                            <td col-sm-1>
                                <a href="{{ route('categoryinputs.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                                <a href="{{ route('categoryinputs.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                                <form method="POST" action="{{ route('categoryinputs.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                                    {{ method_field('DELETE') }}
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'categoryinput','name'=>'']) }}')">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="pagination-wrapper"> {!! $categoryinputs->render() !!} </div>

            </div>
        </div>
        @endslot
    @endcomponent
@endsection
