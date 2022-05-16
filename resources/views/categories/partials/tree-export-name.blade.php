@unless (empty($categories) || empty($locale))

<ul class="category-tree">
    @foreach ($categories as $category)
    <li>
        @if(isset($category->trans[$locale]))
        {{ $category->trans[$locale] }}
        @else
        {{ $category->name ?? '' }}
        @endif

        @include('categories.partials.tree-export-name', [ 'categories' => $category->children, 'locale'=>$locale ])
    </li>
    @endforeach
</ul>


@endunless