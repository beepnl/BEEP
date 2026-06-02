<div class="form-group">
    {{ html()->label('Identifier name (EN, lowercase, no spaces):', 'name') }}
    {{ html()->text('name')->class('form-control')->autofocus(true) }}
    {!! $errors->first('name') !!}
</div>

<div class="form-group">
    {{ html()->label('OR multiple textual rows with categories (EN). Use a TAB to indicate a new child category:', 'names') }}
    {{ html()->textarea('names')->class('form-control') }}
    {!! $errors->first('names') !!}
</div>

<div class="form-group">
    {{ html()->label('Parent:', 'parent_id') }}
    {{ html()->select('parent_id', $categories)->class('form-control') }}
    {!! $errors->first('parent_id') !!}
</div>

<dl>
	<dt>{{ __('crud.description') }}:</dt>
    <dd>{{ html()->text('description')->placeholder('Description in English')->class('form-control') }}</dd>
    <dt>{{ __('crud.type') }}:</dt>
    <dd>{{ html()->select('category_input_id', App\CategoryInput::selectList(), isset($category->category_input_id) ? $category->category_input_id : 32)->class('form-control') }}</dd>
    <dt>{{ __('general.Physical_quantity') }} ({{ __('general.unit') }}):</dt>
    <dd>{{ html()->select('physical_quantity_id', App\PhysicalQuantity::selectList())->class('form-control') }}</dd>
    <dt>{{ __('beep.required_in_inspection') }}:</dt>
    <dd>
        <div class="radio">
            <label><input name="required" type="radio" value="1" {{ (isset($category) && 1 == $category->required) ? 'checked' : '' }}> Yes</label>
        </div>
        <div class="radio">
            <label><input name="required" type="radio" value="0" @if (isset($category)) {{ (0 == $category->required) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
        </div>
    </dd>
</dl>