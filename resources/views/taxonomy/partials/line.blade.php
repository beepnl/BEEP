@unless (empty($categories))
    @foreach ($categories as $category)

        <p>
        {{ $category->ancName($locale, '.') }}{{ $category->trans[$locale] ?? $category->name ?? '' }}
        </p>

        @include('taxonomy.partials.line', [ 'categories' => $category->children, 'locale'=>$locale ])
    @endforeach
@endunless