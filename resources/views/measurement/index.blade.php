@extends('layouts.app')

@section('page-title') {{ __('crud.management', ['item'=>__('beep.measurement')]) }}
@endsection

@section('content')
    @component('components/box')
        @slot('title')
            {{ __('crud.overview', ['item'=>__('beep.measurement')]) }}
        @endslot

        @slot('action')
            @permission('role-create')
                <a href="{{ route('measurement.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ __('crud.add', ['item'=>__('beep.measurement')]) }}
                </a>
            @endpermission
        @endslot

        @slot('bodyClass')
        @endslot

        @slot('body')

        <script type="text/javascript">
            $(document).ready(function() {
                $("#table-measurement").DataTable(
                    {
                    "language": 
                        @php
                            echo File::get(public_path('js/datatables/i18n/'.LaravelLocalization::getCurrentLocaleName().'.lang'));
                        @endphp
                    ,
                    "order": 
                    [
                        [ 1, "asc" ]
                    ],
                });
            });
        </script>


        <table id="table-measurement" class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Abbreviation</th>
                    <th>Physical Quantity</th>
                    <th>Show In Charts / Dials / Alerts</th>
                    <th>Weather</th>
                    <th>Future</th>
                    <th>Source</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Color</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($measurement as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->abbreviation }}</td>
                    <td>{{ $item->pq_name_unit }}</td>
                    <td>{{ isset($item->show_in_charts) && 1 == $item->show_in_charts ? 'Yes' : 'No' }} / {{ isset($item->show_in_dials) && 1 == $item->show_in_dials ? 'Yes' : 'No' }} / {{ isset($item->show_in_alerts) && 1 == $item->show_in_alerts ? 'Yes' : 'No' }}</td>
                    <td>{{ isset($item->weather) && 1 == $item->weather ? 'Yes' : 'No' }}</td>
                    <td>{{ isset($item->future) && 1 == $item->future ? 'Yes' : 'No' }}</td>
                    <td>{{ isset($item->data_source_type) ? App\Measurement::$data_source_types[$item->data_source_type] : '-' }}</td>
                    <td>{{ $item->min_value }}</td>
                    <td>{{ $item->max_value }}</td>
                    <td><span style="background-color: #{{ $item->hex_color }}; border: 1px solid #000; border-radius: 4px; display: inline-block; width: 20px; height: 20px; vertical-align: middle;"></span> #{{ $item->hex_color }}</td>
                    <td col-sm-1>
                        <a href="{{ route('measurement.show', $item->id) }}" title="{{ __('crud.show') }}"><button class="btn btn-default"><i class="fa fa-eye" aria-hidden="true"></i></button></a>

                        <a href="{{ route('measurement.edit', $item->id) }}" title="{{ __('crud.edit') }}"><button class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>

                        <form method="POST" action="{{ route('measurement.destroy', $item->id) }}" accept-charset="UTF-8" style="display:inline">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger pull-right" title="Delete" onclick="return confirm('{{ __('crud.sure',['item'=>'measurement','name'=>'']) }}')">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @endslot
    @endcomponent

    @component('components/box')
        @slot('title')
            CSV Data (paste this in a text file, call it something.csv and open in Excel)
        @endslot

        @slot('$bodyClass')
        @endslot

        @slot('body')

<textarea style="width: 100%;" rows="15">
"Abbreviation","Physical quantity","Unit","Min","Max","HEX Color","Weather related","Show in charts","Show in Dials","Show in alerts"
@foreach ($measurement->sortBy('abbreviation') as $m)
"{{ $m->abbreviation }}","{{ $m->pq }}","{{ $m->unit }}",{{ $m->min_value }},{{ $m->max_value }},"#{{ $m->hex_color }}",{{ $m->weather }},{{ $m->show_in_charts }},{{ $m->show_in_dials }},{{ $m->show_in_alerts }}
@endforeach
</textarea>

        @endslot
    @endcomponent
@endsection
