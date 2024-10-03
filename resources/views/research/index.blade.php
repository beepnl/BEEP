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
                        [ 2, "asc" ]
                    ],
                });
            });
        </script>


        <table id="table-research" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th col-xs-1>Image</th>
                    <th col-xs-1>Visible / Invite only</th>
                    <th col-xs-2>Name</th>
                    <th col-xs-2>Description</th>
                    <th col-xs-1>Type</th>
                    <th col-xs-1>Institution</th>
                    <th col-xs-1>Type Of Data Used</th>
                    <th col-xs-1>Timespan</th>
                    <th col-xs-1>Checklists</th>
                    <th col-xs-1>User consent</th>
                    <th style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($research as $item)
                <tr>
                    {{-- <td>{{ $loop->iteration or $item->id }}</td> --}}
                    <td><a href="{{ route('research.show', $item->id) }}">@if(isset($item->thumb_url))<img src="{{$item->thumb_url}}" style="width:40px; height: 40px; border-radius: 20%; border: 1px solid #333; display: inline-block; overflow: hidden;">@endif</a></td>
                    <td>{{ $item->visible  ? __('general.Yes') : __('general.No') }} / {{ $item->on_invite_only ? __('general.Yes') : __('general.No') }}</td>
                    <td><a href="{{ route('research.show', $item->id) }}">{{ $item->name }}<br>(ID: {{ $item->id }})</a></td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->type }}</td>
                    <td>{{ $item->institution }}</td>
                    <td>{{ $item->type_of_data_used }}</td>
                    <td>{{ substr($item->start_date, 0, 10).' - '.substr($item->end_date, 0, 10) }}</td>
                    <td>{{ $item->checklists->pluck('name')->join(', ') }}</td>
                    <td>{{ $item->users->count() }}</td>
                    <td>
                        <a href="{{ route('research.show', $item->id) }}" title="{{ __('crud.view') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
                        <a href="{{ route('research.consent', $item->id) }}" title="User consents"><button class="btn btn-default"><i class="fa fa-user" aria-hidden="true"></i></button></a>
                        
                        @if (Auth::user()->hasRole('superadmin') || Auth::user()->researchesOwned()->where('id', $item->id)->count() == 1)
                        <a href="{{ route('research.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('research.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'Research','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @endslot
    @endcomponent
@endsection
