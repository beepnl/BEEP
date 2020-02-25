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

    @role('superadmin')
    @if(isset($category))
        <div class="pull-right">
            <a class="btn btn-primary btn-sm" href="{{ route('categories.duplicate', $category->id) }}" title="Duplicate category"><i class="fa fa-copy"></i></a>
            <a class="btn btn-primary btn-sm" href="{{ route('categories.fix', $category->id) }}" title="Fix category"><i class="fa fa-refresh"></i></a>
        </div>
    @endif
    @endrole
    
</ol>