
    @foreach (App\Language::all() as $lang)
        <dt>
        	{{ $lang->name_english }}:
        </dt>
        <dd>
        	{!! Form::text('language['.$lang->abbreviation.']', $translations->get($lang->id), array('placeholder' => $lang->name,'class' => 'form-control')) !!}
        </dd>
    @endforeach

