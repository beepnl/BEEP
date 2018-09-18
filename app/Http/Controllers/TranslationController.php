<?php

namespace App\Http\Controllers;

use App\Translation;
use App\Category;
use App\Language;
use Illuminate\Http\Request;
use Kalnoy\Nestedset\Collection; 

class TranslationController extends Controller
{
    public function index()
	{
		return view('translations.index');
	}

	public function edit(Language $language)
	{
		$categories = $this->getCategoryOptions(null);

		return view('translations.edit', compact('categories','language'));
	}


	public function update(Language $language, Request $request)
	{
		$translation_category = $request->input('translation_category');
		$count = 0;
		if (isset($translation_category))
		{
			foreach ($translation_category as $cat_id => $translation) 
			{
				if (isset($translation))
				{
					$translation_name = Category::find($cat_id);
					if (isset($translation_name))
						$count += Translation::saveText($language->abbreviation, $translation_name->name, 'category', $translation);
				}
			}
		}

		if ($count > 0)
			return redirect()->route('translations.edit', [ $language->id ])->with('success', "$count translations successfully updated!");

		return redirect()->route('translations.edit', [ $language->id ])->with('error', "No translations updated.");
	}



	/**
     * @param Collection $items
     *
     * @return static
     */
    protected function makeOptions(Collection $items)
    {
        $options = [];

        foreach ($items as $item)
        {
            $options[$item->getKey()] = ['depth'=>$item->depth, 'name'=>$item->name];
        }

        return $options;
    }

	/**
	 * @param Category $except
	 *
	 * @return CategoriesController
	 */
	protected function getCategoryOptions($except = null)
	{
		/** @var \Kalnoy\Nestedset\QueryBuilder $query */
		$query = Category::select('id', 'name')->withDepth();

		if ($except)
		{
			$query->whereNotDescendantOf($except)->where('id', '<>', $except->id);
		}

		return $this->makeOptions($query->get());
	}
}
