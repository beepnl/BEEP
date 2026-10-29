@extends('form')

@section('title')
    {{ $category->title }}
@overwrite

@section('body')
    {{ html()->modelForm($category, 'PATCH', route('categories.update', $category->getKey()))->open() }}
        
        @include('categories.partials.form')

        <div class="form-group">
            {{ html()->input('submit')->value('Save')->class('btn btn-primary') }}
        </div>
    {{ html()->closeModelForm() }}
@overwrite