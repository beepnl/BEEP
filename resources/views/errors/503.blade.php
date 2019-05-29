@extends('layouts.app')

@section('body-class')login-page
@endsection

@section('content')
    <div class="login-box">
	    <div class="login-logo">
	        <a href="/admin"><img src="/img/beep-icon-logo.svg"></a> 
	    </div>

	    <div class="login-box-body">
	        <h3 class="login-box-msg">Beep is in maintenance</h3>

	    	<p>Please try again later.</p>
	    	<p>Beep right back...</p>
	    	<br>
	    	<br>
	    	<br>
	    	<br>
	    	<br>
	    	<br>

	    	<button class="btn btn-primary btn-block" onclick="alert('BEEP BEEP');">BEEP</button>
	    </div>
	</div>
@endsection