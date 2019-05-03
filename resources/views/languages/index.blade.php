@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.language')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.language')]) }}
        @endslot

        @slot('action')
            @permission('language-create')
                <a href="{{ route('languages.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.language')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-languages").DataTable(
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


        <table id="table-languages" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Name English</th><th>Icon</th><th>Abbreviation</th><th>Abbr. two char.</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($languages as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td>{{ $item->name }}</td><td>{{ $item->name_english }}</td>
                    <td><img src="/img/{{ $item->icon }}" style="width: 30px;"> {{ $item->icon }}</td>
                    <td>{{ $item->abbreviation }}</td>
                    <td>{{ $item->twochar }}</td>
                    <td col-sm-1>
                        <a href="{{ route('languages.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        @permission('language-edit')
                        <a href="{{ route('languages.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
                        @endpermission

                        @permission('language-delete')
                        <form method="POST" action="{{ route('languages.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'language','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                        @endpermission
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $languages->render() !!} </div>

        @endslot
    @endcomponent
@endsection
