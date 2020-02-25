@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.Image')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.Image')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('image.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.Image')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-image").DataTable(
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


        <table id="table-image" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>File</th><th>Description</th><th>Type</th><th>Height</th><th>Width</th><th>Size Kb</th><th>Date</th><th>User Id</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($image as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>
                        <img src="{{ $item->thumb_url }}" style="width: 50px; height: auto;" title="{{ $item->file }}">
                    </td>
                    <td>{{ $item->description }}</td><td>{{ $item->type }}</td><td>{{ $item->height }}</td><td>{{ $item->width }}</td><td>{{ $item->size_kb }}</td><td>{{ $item->date }}</td><td>{{ $item->user_id }}</td>
                    <td col-sm-1>
                        <a href="{{ route('image.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('image.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('image.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'Image','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $image->render() !!} </div>

        @endslot
    @endcomponent
@endsection
