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
        <h3 class="login-box-msg">BEEP Management login</h3>

        <form class="form user-form" role="form" method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}


            @if ($errors->has('email') || $errors->has('password'))
                <div class="alert alert-error">
                    <p>{{ $errors->first('email') }}{{ $errors->first('password') }}</p>
                </div>
            @endif

            <div class="form-group has-feedback">
                <input type="email" class="form-control" id="email" name="email" placeholder="E-mail address" autocorrect="off" autocapitalize="none" required="required" value="{{ old('email') }}" autofocus/>
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" id="password" name="password" ng-model="fields.login.password" placeholder="Password" required="required" />
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>

            {{-- <div class="row">
                <div class="col-xs-12">
                  <div class="checkbox icheck">
                    <label>
                      <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember me
                    </label>
                  </div>
                </div>
            </div> --}}
            <div class="row">
                <div class="col-xs-12">
                  <button type="submit" class="btn btn-primary btn-block btn-flat">Login</button>
                </div>
            </div>

        </form>
        <!--div class="social-auth-links text-center">
          <p>- OR -</p>
          <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in using
            Facebook</a>
          <a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i> Sign in using
            Google+</a>
        </div-->
        <!-- /.social-auth-links -->

        <hr>
        <a href="{{ route('password.request') }}" title="I forgot my password">I forgot my password</a>

    </div>
</div>

{{-- <script src="webapp/vendor/admin-lte/plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="webapp/vendor/admin-lte/bootstrap/js/bootstrap.min.js"></script>
<script src="webapp/vendor/admin-lte/plugins/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script> --}}

@endsection
