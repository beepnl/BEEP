@extends('layouts.app')

@section('page-title') BEEP database overview
@endsection

@section('content')

	
	<div class="row">

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['users'] }} / {{ $data['newusers'] }}</h3>
					<p>Total users / new last 7 days</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
				<a href="/users" class="small-box-footer">
	              Users <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['hourusers'] }} / {{ $data['dayusers'] }}</h3>
					<p>Active users last hour / day</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
				<a href="/users" class="small-box-footer">
	              Users <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['activeusers'] }} / {{ $data['qrtusers'] }}</h3>
					<p>Active users last month / 3 months</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
				<a href="/users" class="small-box-footer">
	              Users <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['yearusers'] }}</h3>
					<p>Active users last 365 days</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
				<a href="/users" class="small-box-footer">
	              Users <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

	</div>
	<div class="row">

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3>{{ $data['locations'] }}</h3>
					<p>Apiaries</p>
				</div>
				<div class="icon">
					<i class="fa fa-map-marker"></i>
				</div>
				<a href="#" class="small-box-footer">
	              No extra info
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3>{{ $data['hives'] }}</h3>
					<p>Hives</p>
				</div>
				<div class="icon">
					<i class="fa fa-archive"></i>
				</div>
				<a href="#" class="small-box-footer">
	               No extra info
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3>{{ $data['queens'] }}</h3>
					<p>Queens</p>
				</div>
				<div class="icon">
					<i class="fa fa-forumbee"></i>
				</div>
				<a href="#" class="small-box-footer">
	               No extra info
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3>{{ $data['frames'] }}</h3>
					<p>Hive layer frames</p>
				</div>
				<div class="icon">
					<i class="fa fa-align-justify fa-rotate-90"></i>
				</div>
				<a href="#" class="small-box-footer">
	               No extra info
	            </a>
			</div>
		</div>

	</div>
	<div class="row">

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>{{ $data['checklists'] }}</h3>
					<p>Checklists</p>
				</div>
				<div class="icon">
					<i class="fa fa-list"></i>
				</div>
				<a href="/checklists" class="small-box-footer">
	               Checklists <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>{{ $data['inspections'] }}</h3>
					<p>Inspections</p>
				</div>
				<div class="icon">
					<i class="fa fa-check-circle"></i>
				</div>
				<a href="/inspections" class="small-box-footer">
	               Inspections <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

	</div>

@endsection