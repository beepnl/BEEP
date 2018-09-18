<ol class="breadcrumb col-xs-12">
    @foreach ($category->getAncestors() as $ancestor)
        <li>
            {{-- <a href="{{ route('categories.show', [ $ancestor->getKey() ]) }}"> --}}
                {{ $ancestor->name }}
            {{-- </a> --}}
        </li>
    @endforeach

    <li class="active">{{ $category->name }}</li> 
    @permission('taxonomy-create')
        <li>
	        <a class="btn btn-primary btn-sm" href="{{ route('categories.create', isset($category) ? ['parent_id='.$category->id] : []) }}"><i class="fa fa-plus"></i> {{ __('crud.add', ['item'=>__('general.category')]) }}</a>
	    </li>
    @endpermission
    
</ol>