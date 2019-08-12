@extends('layouts.app')

@section('page-title') BEEP database overview
@endsection

@section('content')

	@if ($connection !== true)
		<div class="alert alert-danger">
			No InfluxBD connection: {{ substr($connection, 0, 200) }}...
		</div>
	@endif

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
					<p>Active users last hour / since yesterday</p>
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

		@if ($checklist_details)

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>{{ $data['checklists'] }} / {{ $data['checklists_edited'] }}</h3>
					<p>Checklists total / Edited</p>
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
					<h3>{{ $data['inspections'] }} / {{ $data['itemsperinspection'] }}</h3>
					<p>Inspections / Items per inspection</p>
				</div>
				<div class="icon">
					<i class="fa fa-check-circle"></i>
				</div>
				<a href="/inspections" class="small-box-footer">
	               Inspections <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>Top 5 lists size</h3>
					<p>
					@foreach($data['checklist_categories_max'] as $value)
						<span>{{ $value->count }}, </span> 
					@endforeach
					</p>
				</div>
				<div class="icon">
					<i class="fa fa-list"></i>
				</div>
				<a href="/checklists" class="small-box-footer">
	               Checklists <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		{{-- <div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3>Btm 5 list itm size</h3>
					<p>
					@foreach($data['checklist_categories_min'] as $value)
						<span>{{ $value->count }}, </span> 
					@endforeach
					</p>
				</div>
				<div class="icon">
					<i class="fa fa-list"></i>
				</div>
				<a href="/checklists" class="small-box-footer">
	               Checklists <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div> --}}

		@endif

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-orange">
				<div class="inner">
					<h3>{{ $data['sensors'] }}</h3>
					<p>{{ __('general.Sensors') }}</p>
				</div>
				<div class="icon">
					<i class="fa fa-wifi"></i>
				</div>
				<a href="#" class="small-box-footer">
	              No extra info
	            </a>
			</div>
		</div>

		

	</div>


	<div class="row">

		@if ($checklist_details)
		
		<div class="col-lg-4 col-xs-12">
		<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
					<h3>Fixed inspection variable usage</h3>
					<p>(taken into account {{ $data['inspection_valid_user_count']}} users with > 10 inspections)</p>
					<div style="overflow: auto; height: 200px;">
						@foreach($data['ins_vars'] as $name => $value)
							<p style="line-height: 10px;">
								<span style="display: inline-block; width: 50px; font-weight: bold;">{{ $value['count'] }}:</span> 
								<span><i class="glyphicon glyphicon-{{ $value['glyphicon'] }}"></i></span>
								{{ $name }}
							</p>
						@endforeach
					</div>
				</div>
				<div class="icon">
					<i class="fa fa-check-circle"></i>
				</div>
				<a href="/inspections" class="small-box-footer">
	               Inspections <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>

		<div class="col-lg-4 col-xs-12">
		<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
					<h3>Dynamic inspection item top {{count($data['terms'])}}</h3>
					<p>(taken into account {{ $data['inspection_valid_user_count']}} users with > 10 inspections)</p>
					<div style="overflow: auto; height: 200px;">
						@foreach($data['terms'] as $name => $value)
							<p style="line-height: 10px;">
								<span style="display: inline-block; width: 50px; font-weight: bold;">{{ $value['count'] }}:</span> 
								<span><i class="glyphicon glyphicon-{{ $value['glyphicon'] }}"></i></span>
								{{ $name }}
							</p>
						@endforeach
					</div>
				</div>
				<div class="icon">
					<i class="fa fa-check-circle"></i>
				</div>
				<a href="/inspections" class="small-box-footer">
	               Inspections <i class="fa fa-arrow-circle-right"></i>
	            </a>
			</div>
		</div>
		
		<div class="col-lg-4 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-orange">
				<div class="inner">
					<h3>{{ $data['measurements'] }} {{ __('general.Measurements') }}</h3>
					<div style="overflow: auto; height: 230px;">
						<dl class="dl-horizontal">
						@foreach( $data['measurement_details'] as $name => $count)
							<dt>{{ $name }}</dt>
							<dd>{{ $count }}</dd>
						@endforeach
						</dl>
					</div>
				</div>
				<div class="icon">
					<i class="fa fa-wifi"></i>
				</div>
				<a href="#" class="small-box-footer">
	               No extra info
	            </a>
			</div>
		</div>


		@else

			<div class="col-lg-12 col-xs-12">
			<!-- small box -->
				<div class="small-box bg-red">
					<div class="inner">
						<h3>Get inspection variable usage and sensor measurements</h3>
					</div>
					<div class="icon">
						<i class="fa fa-check-circle"></i>
					</div>
					<a href="/dashboard?checklist_details=1" class="small-box-footer">
		               Load data <i class="fa fa-arrow-circle-right"></i>
		            </a>
				</div>
			</div>



		@endif

	</div>


@endsection