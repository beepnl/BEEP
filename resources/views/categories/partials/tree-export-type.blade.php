@unless (empty($categories) || empty($type))

@foreach ($categories as $category)
    
    @if ($type == 'input' && isset($category->category_input_id))
    {{ $category->inputType->name_plus() }}
    @elseif ($type == 'unit')
    {{ $category->unit }}
    @endif
    
    <br>
    
    @include('categories.partials.tree-export-type', [ 'categories' => $category->children, 'type'=>$type ])

@endforeach

@endunless