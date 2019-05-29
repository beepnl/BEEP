@extends('layouts.app')

@section('body-class')login-page
@endsection

@section('content')
    <div class="login-box">
	    <div class="login-logo">
	        <a href="/admin"><img src="/img/beep-icon-logo.svg"></a> 
	    </div>

	    <div class="login-box-body">
	        <h3 class="login-box-msg">Error</h3>
	        <p><strong>Page not found</strong></p>
	        <br>
	    	<br>
	    	<br>
	    	<br>
	    	<br>
	    	<a class="btn btn-primary btn-block" href="https://app.beep.nl">Open app</a>
	    	<br>
	    	<button class="btn btn-primary btn-block" onclick="history.back();">Go back</button>
	    </div>
	</div>
@endsection