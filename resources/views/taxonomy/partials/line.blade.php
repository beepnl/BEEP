@unless (empty($categories))
    @foreach ($categories as $category)

        <p>
        {{ $category->ancName($locale, '.') }}{{ isset($category->trans[$locale]) ? $category->trans[$locale] : isset($category->name) ? $category->name : '' }}
        </p>

        @include('taxonomy.partials.line', [ 'categories' => $category->children, 'locale'=>$locale ])
    @endforeach
@endunless