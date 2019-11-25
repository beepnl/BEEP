@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.Researches')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.Research')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('research.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add_a', ['item'=>__('beep.Research')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
            table-responsive
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-research").DataTable(
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


        <table id="table-research" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Description</th><th>Type</th><th>Institution</th><th>Type Of Data Used</th><th>Start Date</th><th>End Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($research as $item)
                <tr>
                    <td>{{ $loop->iteration or $item->id }}</td>
                    <td><a href="{{$item->url}}" target="_blank">@if(isset($item->image))<img src="{{$item->image}}" style="width:30px; height: 30px; border-radius: 20%; border: 1px solid #333; display: inline-block;">@endif {{ $item->name }}</a></td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->type }}</td>
                    <td>{{ $item->institution }}</td>
                    <td>{{ $item->type_of_data_used }}</td>
                    <td>{{ $item->start_date }}</td>
                    <td>{{ $item->end_date }}</td>
                    <td style="min-width: 100px;">
                        <a href="{{ route('research.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('research.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'Research','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper"> {!! $research->render() !!} </div>

        @endslot
    @endcomponent
@endsection
