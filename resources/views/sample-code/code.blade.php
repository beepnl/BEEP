@extends('layouts.app')

@section('head')
@endsection

@section('body-class')login-page
@endsection

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="/admin"><img src="/img/beep-icon-logo.svg"></a> 
    </div>

    <div class="login-box-body">
        <h3 class="login-box-msg">Enter sample code</h3>

        <form class="form user-form" role="form" method="POST" action="{{ route('sample-code.check') }}">
            {{ csrf_field() }}


            @if ($message = Session::get('error'))
            <div class="alert alert-danger">
                <p>{{ $message }}</p>
            </div>
            @endif

            @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
            @endif

            <div class="form-group has-feedback">
                <input type="samplecode" class="form-control" id="samplecode" name="samplecode" placeholder="Samplecode" autocorrect="off" style="letter-spacing: 5px; font-weight: bold; text-transform: uppercase;" autocapitalize="characters" required="required" value="{{ old('samplecode') }}" autofocus/>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-12">
                  <button type="submit" class="btn btn-primary btn-block btn-flat">Check sample code</button>
                </div>
            </div>

        </form>
       
    </div>
</div>

@endsection
