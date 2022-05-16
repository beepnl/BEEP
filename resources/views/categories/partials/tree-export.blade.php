@unless (empty($categories))

@php
    $locale = LaravelLocalization::getCurrentLocale();
@endphp

<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th style="padding: 10px;">Category ({{ strtoupper($locale) }})</th>
            <th style="padding: 10px;">Unit</th>
            <th style="padding: 10px;">Input type</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 10px;">
                <ul class="category-tree">
                    @foreach ($categories as $category)
                        <li>
                            @if(isset($category->trans[$locale]))
                            {{ $category->trans[$locale] }}
                            @else
                            {{ $category->name or '' }}
                            @endif

                            @include('categories.partials.tree-export-name', [ 'categories' => $category->children, 'locale' => $locale ])
                        </li>
                    @endforeach
                </ul>
            </td>
            <td style="padding: 10px;">
                @include('categories.partials.tree-export-type', [ 'categories' => $categories, 'type'=>'unit' ])
            </td>
            <td style="padding: 10px;">
                @include('categories.partials.tree-export-type', [ 'categories' => $categories, 'type'=>'input' ])
            </td>
        </tr>
    </tbody>
</table>

@endunless