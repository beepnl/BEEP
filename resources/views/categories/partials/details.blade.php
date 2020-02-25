<div>

    @if (count($errors) > 0)
        <div class=" col-xs-12 alert alert-danger">
            {{ __('crud.input_err') }}:<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {!! Form::model($category, ['method' => 'PATCH', 'route' => ['categories.update', $category->id], 'files'=>'true']) !!}
    
    <div class="row">
        <div class="col-xs-12">
            <dl class="dl-horizontal">
                <dt>Identifier &amp; icon:</dt>
                <dd>
                    <div class="row">
                        <div class="col-md-6">
                            @if (isset($category->icon))
                            <img src="{{ Storage::disk('icons')->url($category->icon) }}" style="width:50px; height:50px;">
                            @endif
                            <strong>
                            {!! Form::text('name', null, array('placeholder' => 'Programmatic name (make sure it not already used!!)', 'class' => 'form-control', 'title'=>'Programmatic (code) name (make sure it not  used in the code, else it will break the app!!)')) !!}
                            </strong>
                        </div>
                        <div class="col-md-6">
                            {!! Form::file('icon', array('class' => 'btn btn-default', 'style'=>'display: inline-block;', 'title'=>'Icon')) !!}
                        </div>
                    </div>
                </dd>
                <dt>Parent &amp; type:</dt>
                <dd>
                    <div class="row">
                        <div class="col-md-6">
                            {!! Form::select('parent_id', $categories, null, [ 'class' => 'form-control' ]) !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::select('type', App\Category::$types, null, array('class' => 'form-control')) !!}
                        </div>
                    </div>
                </dd>
                
                <dt>{{ __('general.Translations') }}</dt>
                <dd>
                    @include('categories.partials.translations', [ 'translations' => $category->translations() ])
                </dd>
            </dl>
            
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <dl class="dl-horizontal">
                <dt>{{ __('crud.description') }}:</dt>
                <dd>{!! Form::text('description', null, array('placeholder' => 'Description in English', 'class' => 'form-control')) !!}</dd>
                <hr>
                <dt>Input {{ __('crud.type') }}:</dt>
                <dd>{!! Form::select('category_input_id', App\CategoryInput::selectList(), null, array('class' => 'form-control')) !!}</dd>
                <dt>{{ __('general.Physical_quantity') }} ({{ __('general.unit') }}):</dt>
                <dd>{!! Form::select('physical_quantity_id', App\PhysicalQuantity::selectList(), null, array('class' => 'form-control')) !!}</dd>
                <dt>{{ __('general.Source') }}:</dt>
                <dd>{!! Form::text('source', null, array('placeholder' => __('general.Source').' (http://)', 'class' => 'form-control')) !!}</dd>
                <dt>{{ __('beep.required_in_inspection') }}:</dt>
                <dd>
                    <div>
                        <div class="radio">
                            <label><input name="required" type="radio" value="1" {{ (isset($category) && 1 == $category->required) ? 'checked' : '' }}> {{ __('general.Yes') }} </label>
                        </div>
                        <div class="radio">
                            <label><input name="required" type="radio" value="0" @if (isset($category)) {{ (0 == $category->required) ? 'checked' : '' }} @else {{ 'checked' }} @endif> {{ __('general.No') }}</label>
                        </div>
                    </div>
                </dd>
                {{-- <hr> --}}
                {{-- <dt>Old (fixed) category ID:</dt>
                <dd>{!! Form::text('old_id', null, array('placeholder' => 'Id of former category in database', 'class' => 'form-control')) !!}</dd> --}}
            </dl>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-center">
            <button type="submit" class="btn btn-primary btn-block btn-block">{{ __('crud.save') }}</button>
        </div>
    </div>
    
    {!! Form::close() !!}

    <div class="row">
        <div class="col-xs-12 text-center">
            <hr>
            {{ __('general.Category').' '.__('general.usage').': '.$category->useAmount().'x' }}
            @if ($category->isSystem())
                System node (cannot be removed)

            @else

                @permission('taxonomy-delete')
                @if ($category->useAmount() == 0)

                {!! Form::open(['method' => 'DELETE','route' => ['categories.destroy', $category->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("'.__('crud.sure',['item'=>__('general.category'),'name'=>'\''.$category->name.'\'']).'")', 'title'=>'Delete category (and all it\'s descendants)']) !!}
                {!! Form::button('<i class="fa fa-trash-o"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right']) !!}
                {!! Form::close() !!}

                {!! Form::open(['method' => 'DELETE','route' => ['categories.pop', $category->id], 'style'=>'display:inline', 'onsubmit'=>'return confirm("Are you sure you want to pop category &quot;'.$category->name.'&quot; out in between of the tree (and move all it&quot;s descendants to its parent?")', 'title'=>'Pop category (and move all it\'s descendants to its parent)']) !!}
                {!! Form::button('<i class="fa fa-minus-circle"></i>', ['type'=>'submit', 'class' => 'btn btn-danger pull-right','style'=>'margin-right: 10px;', ($category->isLeaf() ? 'disabled' : '')]) !!}
                {!! Form::close() !!}
                @endif
                @endpermission

            @endif
        </div>

    </div>

</div>