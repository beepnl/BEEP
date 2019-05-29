@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('general.taxonomy')]) }}
@endsection

@section('content')

    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('general.categories')]) }}
        @endslot

        @slot('action')
            {{-- <a class="btn btn-sm btn-danger" href="{{ route('categories.fix', 1) }}" title="Fix broken links and replace list items with other children than only list_items by labels" >Fix taxonomy</a> --}}
        @endslot

        @slot('bodyClass')

        @endslot

        @slot('body')
        <div class="row">
            <div class="col-sm-4">
                <h4>
                    {{__('general.Categories')}} ({{ App\Category::all()->count() }})
                </h4>

                <input class="form-control" type="text" id="category-tree-search" placeholder="Search">

                <div id="category-tree">
                    @include('categories.partials.tree', ['categories'=>$tree, 'edit_taxonomy'=>true])
                </div>
            </div>

            <div class="col-sm-8">
                <h4>@yield('title')</h4>

                @yield('body')
            </div>
        </div>
        @endslot

    @endcomponent

@endsection