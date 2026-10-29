
    @foreach (App\Language::all() as $lang)
        <dt>
        	{{ $lang->name_english }}:
        </dt>
        <dd>
        	{{ html()->text('language[' . $lang->abbreviation . ']', $translations->get($lang->id))->placeholder($lang->name)->class('form-control') }}
        </dd>
    @endforeach

