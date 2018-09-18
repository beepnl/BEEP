<div class="form-group">
    {!! Form::label('name', 'Identifier name (EN, lowercase, no spaces):') !!}
    {!! Form::text('name', null, [ 'class' => 'form-control', 'autofocus' => true ]) !!}
    {!! $errors->first('name') !!}
</div>

<div class="form-group">
    {!! Form::label('parent_id', 'Parent:') !!}
    {!! Form::select('parent_id', $categories, null, [ 'class' => 'form-control' ]) !!}
    {!! $errors->first('parent_id') !!}
</div>

<dl>
	<dt>{{ __('crud.description') }}:</dt>
    <dd>{!! Form::text('description', null, array('placeholder' => 'Description in English', 'class' => 'form-control')) !!}</dd>
    <dt>{{ __('crud.type') }}:</dt>
    <dd>{!! Form::select('category_input_id', App\CategoryInput::selectList(), null, array('class' => 'form-control')) !!}</dd>
    <dt>{{ __('general.Physical_quantity') }} ({{ __('general.unit') }}):</dt>
    <dd>{!! Form::select('physical_quantity_id', App\PhysicalQuantity::selectList(), null, array('class' => 'form-control')) !!}</dd>
</dl>