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
			<div class="small-box bg-green">
				<div class="inner">
					<h3>{{ $data['store-measurements-total'] }} / {{ $data['store-measurements-201'] }}</h3>
					<p>Data storage req Total / OK (201) req/min<br>TTNv2: {{ $data['store-lora-sensors-ttn-v2'] }} | v3: {{ $data['store-lora-sensors-ttn-v3'] }} | v3pb: {{ $data['store-lora-sensors-ttn-v3-pb'] }} | KPN: {{ $data['store-lora-sensors-kpn'] }} | ?: {{ $data['store-lora-sensors-'] }} | API: {{ $data['store-sensors'] }}</p>
				</div>
				<div class="icon">
					<i class="fa fa-database"></i>
				</div>
			</div>
		</div>


		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-orange">
				<div class="inner">
					<h3>{{ $data['store-measurements-400'] }} / {{ $data['store-measurements-401'] }}</h3>
					<p>Data storage <br>no/unexisting key (400/401) req/min</p>
				</div>
				<div class="icon">
					<i class="fa fa-database"></i>
				</div>
			</div>
		</div>


		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
					<h3>{{ $data['store-measurements-500'] }}</h3>
					<p>Data storage <br>save error (500) req/min</p>
				</div>
				<div class="icon">
					<i class="fa fa-database"></i>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['get-measurements'] }}</h3>
					<p>Get data measurement <br>requests/min</p>
				</div>
				<div class="icon">
					<i class="fa fa-database"></i>
				</div>
			</div>
		</div>
	</div>

	<div class="row">

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['users'] }} / {{ $data['newusers'] }}</h3>
					<p>Total users <br>New last 7 days</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['hourusers'] }} / {{ $data['dayusers'] }}</h3>
					<p>Active users <br>Last hour / since yesterday</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['activeusers'] }} / {{ $data['qrtusers'] }}</h3>
					<p>Active users <br>Last month / 3 months</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-primary">
				<div class="inner">
					<h3>{{ $data['yearusers'] }}</h3>
					<p>Active users <br>Last 365 days</p>
				</div>
				<div class="icon">
					<i class="fa fa-user-circle-o"></i>
				</div>
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
			</div>
		</div>

		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-purple">
				<div class="inner">
					<div class="pull-right" style="overflow: auto; height: 50px;">
						@foreach($data['researches'] as $r)
							<p style="line-height: 10px;">
								{{ $r['name'] }}
								<span style="display: inline-block; font-weight: bold;">Consented yes:{{ $r['yes'] }} no:{{ $r['no'] }}</span> 
							</p>
						@endforeach
					</div>
					<h3>{{ count($data['researches']) }}</h3>
					<p>{{ __('beep.Researches') }}</p>
				</div>
				<div class="icon">
					<i class="fa fa-search"></i>
				</div>
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
			</div>
		</div>


		@elseif (Auth::user()->hasRole(['superadmin','admin']))

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