@extends('layouts.app')

@section('page-title') {{ __('crud.upload', ['item'=>__('beep.SampleCode').' results']) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.upload', ['item'=>'File']) }}
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

            @if ($errors->any())
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <h3>1. Download Sample code Excel template file</h3>
            <a href="{{ $template_url }}">{{ $template_url }}</a>
            <br>
            <br>

            <h3>2. Upload filled Sample code Excel template file</h3>
            <form method="POST" action="{{ route('sample-code.upload-store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}

                <label>Filled Excel template file</label>
                <input type="file" disabled title="NOT IMPLEMENTED YET">

            </form>

            <br>
            <h3>3. Check uploaded data</h3>

        @endslot
    @endcomponent
@endsection
