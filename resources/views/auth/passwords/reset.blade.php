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

        @if ($errors->has('email') || $errors->has('password') || $errors->has('password_confirmation'))
            <div class="alert alert-error">
                <p>{{ $errors->first('email') }}{{ $errors->first('password') }}{{ $errors->first('password_confirmation') }}</p>
            </div>
        @endif

        <form class="form user-form" role="form" method="POST" action="{{ route('password.request') }}">
            {{ csrf_field() }}

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group has-feedback{{ $errors->has('email') ? ' has-error' : '' }}">
                <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" placeholder="E-mail address" required autofocus>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback{{ $errors->has('password') ? ' has-error' : '' }}">
                <input id="password" type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="form-group has-feedback{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            <div class="row">
                <div class="col-xs-12">
                  <button type="submit" class="btn btn-primary btn-block btn-flat">Reset password</button>
                </div>
            </div>
        </form>

        <hr>
        <a href="{{ route('password.email') }}" title="Send reset password link">Send reset password link</a>
        <a href="{{ route('login') }}" title="I remembered my password">I remembered my password</a>
    </div>
</div>
@endsection
