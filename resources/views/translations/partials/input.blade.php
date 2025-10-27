@unless (empty($categories) || !isset($language))
    

<hr>

<h3>Default Alert Rules</h3>
<p>Translate the Default Alert Rules in the 'copy new alert rule' tab of the HCS app.</p>
<br>
<h4>{{ count($alert_rules) }} Default Alert Rules</h4>
        
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-1" >ID</th>
            <th class="col-xs-2" >Name (Animal type)<br>Description</th>
            <th class="col-xs-3" >English translation<br>Description</th>
            <th class="col-xs-2 text-success" >{{$language->name_english}} translation</th>
            <th class="col-xs-4 text-success" >{{$language->name_english}} description</th>
        </tr>
    </thead>
    
    <tbody>
    @foreach ($alert_rules as $r)
        <tr>
            <td>{{ $r->id }}.</td>
            <td>
                <p>{{ $r->name }} @if(isset($r->area_type_id)) ({{ $r->area_type->name }}) @endif</p>
                <p>{{ $r->description }}</p>
            </td>
            <td>
                <p>{{ App\Translation::where('type', 'alert_rule')->where('name', $r->name)->where('language_id', 1)->value('translation') }}<br>
                {{ App\Translation::where('type', 'alert_rule_description')->where('name', $r->description)->where('language_id', 1)->value('translation') }}</p>
            </td>
            <td>
                {!! Form::text("translation_alert_rule[$r->id]", App\Translation::where('type', 'alert_rule')->where('name', $r->name)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
            </td>
            <td>
                {!! Form::text("translation_alert_rule_descr[$r->id]", App\Translation::where('type', 'alert_rule_description')->where('name', $r->description)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
            </td>
        </tr>

    @endforeach
    </tbody>
</table>
<hr>
<br>


<h3>Physical quantities</h3>
<p>Translate the Physical quantities for the HCS app.</p>
NB: Only translate the Physical quantity, not the abbreviation, or unit</p>
<br>
<h4>{{ count($physical_quantities) }} Physical quantities</h4>
        
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-1" >ID</th>
            <th class="col-xs-3" >abbreviation: Physical quantity (unit)</th>
            <th class="col-xs-3" >English translation (reference)</th>
            <th class="col-xs-5 text-success" >{{$language->name_english}} translation</th>
        </tr>
    </thead>
    
    <tbody>
    @foreach ($physical_quantities as $p)

        @php
            $name = isset($p) ? $p->abbreviation.': '.$p->name.' ('.$p->unit.')' : '-';
        @endphp
        
        <tr>
            <td>{{ $p->id }}.</td>
            <td>
                <p>{{ $name }}</p>
            </td>
            <td>
                <p>{{ App\Translation::where('type', 'physical_quantity')->where('name', $p->abbreviation)->where('language_id', 1)->value('translation') }}</p>
            </td>
            <td>
                {!! Form::text("translation_physical_quantity[$p->id]", App\Translation::where('type', 'physical_quantity')->where('name', $p->abbreviation)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
            </td>
        </tr>

    @endforeach
    </tbody>
</table>
<hr>
<br>

<h3>Measurements</h3>
<p>Translate the Physical quantities of the names of sensor measurements in the Data tab of the HCS app.</p>
NB: Only translate the Physical quantity, not the abbreviation, or unit</p>
<br>
<h4>{{ count($measurements) }} Measurements</h4>
        
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-1" >ID</th>
            <th class="col-xs-3" >abbreviation: Physical quantity (unit)</th>
            <th class="col-xs-3" >English translation (reference)</th>
            <th class="col-xs-5 text-success" >{{$language->name_english}} translation</th>
        </tr>
    </thead>
    
    <tbody>
    @foreach ($measurements as $m)

        @php
            $name = isset($m) ? $m->abbreviation.': '.$m->pq_name_unit(false) : '-';
        @endphp
        
        <tr>
            <td>{{ $m->id }}.</td>
            <td>
                <p>{{ $name }}</p>
            </td>
            <td>
                <p>{{ App\Translation::where('type', 'measurement')->where('name', $m->abbreviation)->where('language_id', 1)->value('translation') }}</p>
            </td>
            <td>
                {!! Form::text("translation_measurement[$m->id]", App\Translation::where('type', 'measurement')->where('name', $m->abbreviation)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
            </td>
        </tr>

    @endforeach
    </tbody>
</table>



       <div class="col-xs-12">
        <h3>Checklist items</h3>
        <p>The Checklist item translations below are for the hive checklist / category tree that is available in the app through editing your hive checklist. It contains all the available options in the category tree.</p>
        <h4>How to translate checklist items?</h4>
        <p>Just enter the correct <span class="text-success"><strong>{{ $language->name_english }} translations</strong></span> in the input fields of the '<span class="text-success"><strong>{{$language->name_english}} translation</strong></span>' colomn below. If the same category name is in the tree multiple times, your need to translate it only once. 
            <br>NB: Press the 'Update translations' button to store the translations.
        </p>
        <br>
        <h4>{{ count($categories) }} Categories (inspection checklist list items)</h4>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-xs-1" >ID</th>
                    <th class="col-xs-3" >Category name</th>
                    <th class="col-xs-3" >English translation (reference)</th>
                    <th class="col-xs-5 text-success" >{{$language->name_english}} translation</th>
                </tr>
            </thead>

            <tbody>

            @foreach ($categories as $cat_id => $category)

                @if (isset($category))
                    @php
                        $name  = $category['name'];
                        $depth = intval($category['depth']);
                    @endphp
                    
                    <tr>
                        <td>{{ $cat_id }}.</td>
                        <td>
                            <p style="padding-left: {{ 20*$depth }}px;">{{ $name }}</p>
                        </td>
                        <td>
                            <p style="padding-left: {{ 20*$depth }}px;">{{ App\Translation::where('type', 'category')->where('name', $name)->where('language_id', 1)->value('translation') }}</p>
                        </td>
                        <td>
                            {!! Form::text("translation_category[$cat_id]", App\Translation::where('type', 'category')->where('name', $name)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
                        </td>
                    </tr>
                
                @endif

            @endforeach
        
            </tbody>

        </table>
<hr>
</div>

@endunless