@unless (empty($categories) || !isset($language))
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="col-xs-1" >ID</th>
                <th class="col-xs-3" >Variable names</th>
                <th class="col-xs-3" >English translations (reference)</th>
                <th class="col-xs-5 text-success" >{{$language->name_english}} translations</th>
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
                        <p style="padding-left: {{ 20*$depth }}px;">{{ App\Translation::where('name', $name)->where('language_id', 1)->value('translation') }}</p>
                    </td>
                    <td>
                        {!! Form::text("translation_category[$cat_id]", App\Translation::where('name', $name)->where('language_id', $language->id)->value('translation'), [ 'class' => 'form-control' ]) !!}
                    </td>
                </tr>
            
            @endif

        @endforeach
    
        </tbody>

    </table>
@endunless