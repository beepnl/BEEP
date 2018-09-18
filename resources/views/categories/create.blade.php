@extends('categories.layout')

@section('title')
New category
@overwrite

@section('body')
    {!! Form::model($data, [ 'route' => 'categories.store' ]) !!}
        @include('categories.partials.form')
        <br>
        <div class="form-group">
            {!! Form::submit('Create', [ 'class' => 'btn btn-primary btn-block' ]) !!}
        </div>
    {!! Form::close() !!}
@overwrite