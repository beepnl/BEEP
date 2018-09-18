@unless (empty($categories))
    <ul class="category-tree">
        @foreach ($categories as $category)
            <li data-jstree='{"icon":"glyphicon glyphicon-{{ $category->type == 'system' ? 'lock' : $category->inputTypeIcon() }}", "selected":"{!! (isset($selected) && in_array($category->id, $selected)) !!}", "opened":"{!! (isset($selected) && in_array($category->id, $selected)) !!}", "cat":"{{$category->id}}" }'>

                @if(isset($edit_taxonomy) && $edit_taxonomy == true)
                <a href="{{ route('categories.show', [ $category->getKey() ]) }}">
                @endif
                    
                    @php
                        $locale = LaravelLocalization::getCurrentLocale();
                    @endphp

                    @if(isset($category->trans[$locale]))
                    {{ $category->trans[$locale] }}
                    @else
                    {{ $category->name or '' }}
                    @endif

                @if(isset($edit_taxonomy) && $edit_taxonomy == true)
                </a>
                @endif

                @include('categories.partials.tree', [ 'categories' => $category->children ])
            </li>
        @endforeach
    </ul>
@endunless