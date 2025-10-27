<?php

namespace App\Http\Controllers;

use App\Translation;
use App\Language;
use App\Category;
use App\Measurement;
use App\PhysicalQuantity;
use App\Models\ChartGroup;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Kalnoy\Nestedset\Collection; 
use Cache;

class TranslationController extends Controller
{
    public function index()
	{
		return view('translations.index');
	}

	public function edit(Language $language, Request $request)
	{
		$output_csv   = boolval($request->input('csv', 0));

		$categories = $this->getCategoryOptions(null);
		$measurements = Measurement::all();
		$physical_quantities = PhysicalQuantity::all();
		$alert_rules  = AlertRule::where('default_rule', true)->get();

		return view('translations.edit', compact('measurements','language','physical_quantities','alert_rules','output_csv','categories'));
	}


	public function update(Language $language, Request $request)
	{
		$translation_measurement 		= $request->input('translation_measurement');
		$translation_category    		= $request->input('translation_category');
		$translation_physical_quantity  = $request->input('translation_physical_quantity');
		$translation_alert_rule  		= $request->input('translation_alert_rule');
		$translation_alert_rule_descr  	= $request->input('translation_alert_rule_descr');

		$count = 0;
		if (isset($translation_measurement))
		{
			foreach ($translation_measurement as $m_id => $translation)
			{
				if (isset($translation) && $translation != '')
				{
					$measurement = Measurement::find($m_id);
					//die(print_r(['$m_id'=>$m_id, 'm'=>$measurement]));
					if (isset($measurement))
					{
						$count += Translation::saveText($language->abbreviation, $measurement->abbreviation, 'measurement', $translation);
						$measurement->forgetCache();
					}
				}
			}
		}
		if (isset($translation_physical_quantity))
		{
			foreach ($translation_physical_quantity as $p_id => $translation)
			{
				if (isset($translation) && $translation != '')
				{
					$physical_quantity = PhysicalQuantity::find($p_id);
					//die(print_r(['$p_id'=>$p_id, 'm'=>$physical_quantity]));
					if (isset($physical_quantity))
					{
						$count += Translation::saveText($language->abbreviation, $physical_quantity->abbreviation, 'physical_quantity', $translation);
						$physical_quantity->forgetCache();
					}
				}
			}
		}
		if (isset($translation_category))
		{
			foreach ($translation_category as $cat_id => $translation)
			{
				if (isset($translation) && $translation != '')
				{
					$category = Category::find($cat_id);
					if (isset($category))
					{
						$count += Translation::saveText($language->abbreviation, $category->name, 'category', $translation);
						$category->forgetCache();
					}
				}
			}
		}
		if (isset($translation_alert_rule))
		{
			foreach ($translation_alert_rule as $r_id => $translation)
			{
				if (isset($translation) && $translation != '')
				{
					$alert_rule = AlertRule::find($r_id);
					if (isset($alert_rule))
					{
						$count += Translation::saveText($language->abbreviation, $alert_rule->name, 'alert_rule', $translation);
						$alert_rule->forgetCache();
					}
				}
			}
		}
		if (isset($translation_alert_rule_descr))
		{
			foreach ($translation_alert_rule_descr as $r_id => $translation)
			{
				if (isset($translation) && $translation != '')
				{
					$alert_rule = AlertRule::find($r_id);
					if (isset($alert_rule))
					{
						$count += Translation::saveText($language->abbreviation, $alert_rule->description, 'alert_rule_description', $translation);
						$alert_rule->forgetCache();
					}
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
