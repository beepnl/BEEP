@unless (empty($categories) || !isset($language))
    
       <div class="col-xs-12">
        <h3>Checklist items</h3>
        <p>The Checklist item translations below are for the hive checklist / category tree that is available in the app through editing your hive checklist. It contains all the available options in the category tree.</p>
        <h4>How to translate checklist items?</h4>
        <p>Just enter the correct <span class="text-success"><strong>{{ $language->name_english }} translations</strong></span> in the input fields of the '<span class="text-success"><strong>{{$language->name_english}} translation</strong></span>' colomn below. If the same category name is in the tree multiple times, your need to translate it only once. 
            <br>NB: Press the 'Update translations' button to store the translations.
        </p>
        <br>
        <h4>{{ count($categories) }} Categories (inspection checklist list items)</h4>

<div class="row">
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

        <h3>Physical quantities</h3>
        <p>Physical quantities are names of sensor measurements in the Measurements tab of the BEEP app. They are shown if a user owns a BEEP base or other measurement device.</p>
        <h4>How to translate Physical quantities?</h4>
        <p>Just enter the correct <span class="text-success"><strong>{{ $language->name_english }} translations</strong></span> in the input fields of the '<span class="text-success"><strong>{{$language->name_english}} translation</strong></span>' colomn below.<br>
        NB: Only translate the Physical quantity, not the abbreviation, or unit</p>
        <br>
        <h4>{{ count($measurements) }} Physical quantities</h4>
        
<div class="row">
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


            @foreach ($measurements as $m_id => $abbr)

                @php
                    $m     = App\Measurement::find($m_id);
                    $name  = isset($m) ? $abbr.': '.$m->pq_name_unit(false) : '-';
                @endphp
                
                <tr>
                    <td>{{ $m_id }}.</td>
                    <td>
                        <p>{{ $name }}</p>
                    </td>
                    <td>
                        <p>{{ App\Translation::where('type', 'measurement')->where('name', $abbr)->where('language_id', 1)->value('translation') }}</p>
                    </td>
                    <td>
                        {!! Form::text("translation_measurement[$m_id]", App\Translation::where('type', 'measurement')->where('name', $abbr)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
                    </td>
                </tr>

            @endforeach
            </tbody>
        </table>
<hr>
</div>
@endunless