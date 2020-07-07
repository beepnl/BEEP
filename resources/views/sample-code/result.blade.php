@extends('layouts.app')

@section('head')
@endsection

@section('body-class')login-page
@endsection

@section('content')
    
    <div class="row">
    <div class="col-xs-3"></div> 
    @component('components/box')
        @slot('title') Lab results for sample code: {{ $samplecode->sample_code ?? ''}}
        @endslot

        @slot('titleClass') 
        @endslot

        @slot('class') col-xs-4
        @endslot

        @slot('attribute') style="width:50%;"
        @endslot

        @slot('body')


            @if ($errors->any())
                <ul class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('sample-code.resultsave') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                
                {{ method_field('PATCH') }}
                {{ csrf_field() }}

                <input type="hidden" name="samplecode" value="{{ $samplecode->sample_code ?? ''}}">

                @if(isset($samplecode->sample_note))
                <div class="col-xs-12">
                    <div class="form-group {{ $errors->has('sample_note') ? 'has-error' : ''}}">
                        <label for="sample_note" control-label>{{ 'Sample notes' }}</label>
                        <div>
                            {{ $samplecode->sample_note ?? ''}}
                        </div>
                    </div>
                </div>
                @endif
                @if(isset($samplecode->sample_date))
                <div class="col-xs-12">
                    <div class="form-group {{ $errors->has('sample_date') ? 'has-error' : ''}}">
                        <label for="sample_date" control-label>{{ 'Sample date and time' }}</label>
                        <div>
                            {{ $samplecode->sample_date ?? ''}}
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-xs-12">
                    <div class="form-group {{ $errors->has('test_lab_name') ? 'has-error' : ''}}">
                        <label for="test_lab_name" control-label>{{ 'Lab ID / name' }}</label>
                        <div>
                            <input class="form-control" rows="5" name="test_lab_name" type="text" id="test_lab_name" value="{{ $samplecode->test_lab_name ?? ''}}">
                            {!! $errors->first('test_lab_name', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group {{ $errors->has('test_date') ? 'has-error' : ''}}">
                        <label for="test_date" control-label>{{ 'Test date and time' }}</label>
                        <div>
                            <input class="form-control" name="test_date" type="datetime-local" id="test_date" value="{{ isset($samplecode->test_date) ? substr($samplecode->test_date, 0, 10).'T'.substr($samplecode->test_date, 11,5) : ''}}" >
                            {!! $errors->first('test_date', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group {{ $errors->has('test') ? 'has-error' : ''}}">
                        <label for="test" control-label>{{ 'Test type / specification' }}</label>
                        <div>
                            <textarea class="form-control" rows="5" name="test" type="textarea" id="test" >{{ $samplecode->test ?? ''}}</textarea>
                            {!! $errors->first('test', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group {{ $errors->has('test_result') ? 'has-error' : ''}}">
                        <label for="test_result" control-label>{{ 'Test results' }}</label>
                        <div>
                            <textarea class="form-control" rows="5" name="test_result" type="textarea" id="test_result" >{{ $samplecode->test_result ?? ''}}</textarea>
                            {!! $errors->first('test_result', '<p class="help-block">:message</p>') !!}
                        </div>
                    </div>
                </div>


                <div class="col-xs-12" style="margin-top: 20px;">
                    <div class="form-group">
                        <input class="btn btn-primary btn-block" type="submit" value="{{ 'Save' }}">
                    </div>
                </div>


            </form>


      @endslot
    @endcomponent
    <div class="col-xs-3"></div>
    </div> 
@endsection
