@extends('categories.layout')

@section('title')
New category
@overwrite

@section('body')
    {{ html()->modelForm($data, 'POST', route('categories.store'))->open() }}
        @include('categories.partials.form')
        <br>
        <div class="form-group">
            {{ html()->input('submit')->value('Create')->class('btn btn-primary btn-block') }}
        </div>
    {{ html()->closeModelForm() }}
@overwrite