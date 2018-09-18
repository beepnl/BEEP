@extends('categories.layout')

@section('title')
{{ __('crud.edit') }}
@overwrite

@section('body')
    @include('categories.partials.path')
    @include('categories.partials.details', ['categories'=>$categories])
@overwrite