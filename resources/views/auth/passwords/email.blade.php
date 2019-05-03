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
        <h3 class="login-box-msg">Reset password</h3>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('email'))
            <div class="alert alert-error">
                <p>{{ $errors->first('email') }}</p>
            </div>
        @endif

        <form class="form user-form" role="form" method="POST" action="{{ route('password.email') }}">
            {{ csrf_field() }}

            <div class="form-group has-feedback">
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="E-mail Address" required>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-12">
                  <button type="submit" class="btn btn-primary btn-block btn-flat">Send me the password reset link</button>
                </div>
            </div>
        </form>

        <hr>
        <a href="{{ route('login') }}" title="I remembered my password">I remembered my password</a>

    </div>
</div>
@endsection
