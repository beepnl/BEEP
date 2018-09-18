@extends('form')

@section('title')
    {{ $category->title }}
@overwrite

@section('body')
    {!! Form::model($category, [ 'route' => [ 'categories.update', $category->getKey() ], 'method' => 'PATCH' ]) !!}
        
        @include('categories.partials.form')

        <div class="form-group">
            {!! Form::submit('Save', [ 'class' => 'btn btn-primary' ]) !!}
        </div>
    {!! Form::close() !!}
@overwrite