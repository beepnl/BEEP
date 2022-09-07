@extends('layouts.app')

@section('page-title') {{ __('crud.upload', ['item'=>__('beep.SampleCode').' results']) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            Follow the following steps
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
            @if(isset($template_url))
                <a href="{{ $template_url }}">{{ $template_url }}</a>
            @endif
            <br>
            <br>

            <h3>2. Upload filled Sample code Excel template file</h3>
            <form method="POST" action="{{ route('sample-code.upload-store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}

                <label>Filled Excel template file</label>
                <input type="file" name="sample-code-excel" accept=".xlsx" onchange="submit();">
                
            </form>

            <br>
            <h3>3. Check uploaded data</h3>

            @if(isset($col_names) && isset($data))
            <form method="POST" action="{{ route('sample-code.upload-store') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div style="overflow: scroll;">
                    <table id="table-sample-code-import" class="table table-striped" border="1">
                    <thead>
                        <tr style="border-bottom: 2px solid black;">
                            <th>Ok?</th>
                            @foreach($col_names as $cat_id => $name)
                            <th>{{ $name }} ({{ $cat_id }})</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $id => $item)
                        <tr>
                            <th><input type="checkbox" name="checked[]" value="{{$id}}" checked></th> 
                            @foreach($item as $cat_id => $value)
                            <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>
                <input type="hidden" name="data" value="{{ json_encode($data) }}">
                <button type="submit">Store checked data</button>
            </form>
            @endif

        @endslot
    @endcomponent
@endsection
