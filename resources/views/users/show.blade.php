@extends('layouts.app')

@section('page-title') {{ __('general.User') }}
@endsection

@section('content')

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.name') }}:</label>
                <p>{{ $user->name }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.email') }}:</label>
                <p>{{ $user->email }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Language') }}:</label>
                <p>{{ $user->locale }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('crud.avatar') }}:</label>
                <br>
                <img src="{{ $user->avatar }}" style="width:100px; height:100px; margin-right: 20px; margin-bottom: 10px;" class="img-circle">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Sensors') }}:</label>
                @if(!empty($sensors))
                    <p>
                    @foreach($sensors as $key => $name)
                        <label class="label label-default">{{ $name }}</label>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>{{ __('general.Roles') }}:</label>
                @if(!empty($user->roles))
                    <p>
					@foreach($user->roles as $v)
						<label class="label label-warning">{{ $v->display_name }}</label>
					@endforeach
                    </p>
                @endif
            </div>
        </div>
        @role('superadmin')
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <label>API token:</label>
                <p>{{ $user->api_token }}</p>
            </div>
        </div>
        @endrole
	</div>

    @role('superadmin')

    <h1>User rights</h1>
    <hr>
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-2">
            <div class="form-group">
                <label>Own {{ __('general.Sensors') }} ({{$user->devices()->count()}})</label>
                @if($user->devices()->count() > 0)
                    <p>
                    @foreach($user->devices()->get() as $d)
                        <label class="label label-default"><a href="/sensors/{{ $d->id }}">{{ $d->id }} - {{ $d->name }}</a></label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-2">
            <div class="form-group">
                <label>All {{ __('general.Sensors') }} ({{$user->allDevices()->count()}})</label>
                @if($user->allDevices()->count() > 0)
                    <p>
                    @foreach($user->allDevices()->get()->sortBy('location_name') as $d)
                        <label class="label label-default"><a href="/sensors/{{ $d->id }}">{{ $d->id }} - {{ $d->name }} ({{ $d->location_name }})</a></label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-sm-6 col-md-2">
            <div class="form-group">
                <label>Own {{ __('Apiaries') }} ({{$user->locations()->count()}})</label>
                @if($user->locations()->count() > 0)
                    <p>
                    @foreach($user->locations()->get() as $loc)
                        <label class="label label-default"><a href="/location/{{ $loc->id }}/edit">{{ $loc->id }} - {{ $loc->name }} ({{ $loc->type }}) - Hives: {{ $loc->hives()->count() }}, Insp: {{ $loc->inspection_count }}</a></label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-2">
            <div class="form-group">
                <label>All {{ __('Apiaries') }} ({{$user->allLocations()->count()}})</label>
                @if($user->allLocations()->count() > 0)
                    <p>
                    @foreach($user->allLocations()->get() as $loc)
                        <label class="label label-default"><a href="/location/{{ $loc->id }}/edit">{{ $loc->id }} - {{ $loc->name }} ({{ $loc->type }}) - Hives: {{ $loc->hives()->count() }}, Insp: {{ $loc->inspection_count }}</a></label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-2 col-md-2">
            <div class="form-group">
                <label>Own Checklists ({{$user->checklists()->count()}})</label>
                @if($user->checklists()->count() > 0)
                    <p>
                    @foreach($user->checklists()->get()->sortBy('created_at') as $c)
                        <label class="label label-default">{{ $c->id }} - {{ $c->name }} - {{ $c->type }} - {{ count($c->category_ids) }} items</label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-2 col-md-2">
            <div class="form-group">
                <label>All Checklists ({{$user->allChecklists()->count()}})</label>
                @if($user->allChecklists()->count() > 0)
                    <p>
                    @foreach($user->allChecklists()->get()->sortBy('created_at') as $c)
                        <label class="label label-default">{{ $c->id }} - {{ $c->name }} - {{ $c->type }} - {{ count($c->category_ids) }} items</label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>Collaboration Groups ({{$user->groups()->count()}})</label>
                @if($user->groups()->count() > 0)
                    <p>
                    @foreach($user->groups()->get() as $lr)
                        <label class="label label-default"><a href="/groups/{{ $lr->id }}/edit">{{ $lr->id }} - {{ $lr->name }} - {{ $lr->sensor_name }}</a></label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>All {{ __('Group hives') }} ({{$user->groupHives()->count()}})</label>
                @if($user->groupHives()->count() > 0)
                    <p>
                    @foreach($user->groupHives()->get() as $lr)
                        <label class="label label-default"><a href="/groups/{{ $lr->id }}/edit">{{ $lr->id }} - {{ $lr->name }} - {{ $lr->sensor_name }}</a></label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>Own Hives ({{$user->hives()->count()}})</label>
                @if($user->hives()->count() > 0)
                    <p>
                    @foreach($user->hives()->get() as $a)
                        <label class="label label-default">{{ $a->id }} - {{ $a->getNameAndLocationAttribute() }} - {{ $a->type }} - Insp: {{ $a->inspections()->count() }}</label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>All Hives ({{$user->allHives()->count()}})</label>
                @if($user->allHives()->count() > 0)
                    <p>
                    @foreach($user->allHives()->get() as $a)
                        <label class="label label-default">{{ $a->id }} - {{ $a->getNameAndLocationAttribute() }} - {{ $a->type }} - Insp: {{ $a->inspections()->count() }}</label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        

        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>{{ __('site.AlertRules') }} ({{$user->alert_rules()->count()}})</label>
                @if($user->alert_rules()->count() > 0)
                    <p>
                    @foreach($user->alert_rules()->get()->sortBy('created_at') as $a)
                        <label class="label label-default">{{ $a->id }} - {{ $a->name }}</label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6">
            <div class="form-group">
                <label>{{ __('site.Alerts') }} ({{$user->alerts()->count()}})</label>
                @if($user->alerts()->count() > 0)
                    <p>
                    @foreach($user->alerts()->get()->sortBy('created_at') as $a)
                        <label class="label label-default">{{ $a->id }} - {{ $a->alert_rule_name }}: ({{ $a->alert_function }})</label><br>
                    @endforeach
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endrole

@endsection