<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Taxonomy;
use App\Measurement;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LaravelLocalization;
use Cache;

/**
 * @group Api\TaxonomyController
 * @authenticated
 */
class TaxonomyController extends Controller
{
    
    /**
    api/taxonomy/lists 
    List of current state of the standard categories.
    @authenticated
    */
    public function lists(Request $request)
    {
        $lists = Cache::remember('taxonomy-lists', env('CACHE_TIMEOUT_LONG'), function ()
        {
            $out                = [];
            $out['hivetypes']   = [];

            $hiveTypes = Category::descendentsByRootParentAndName('hive', 'hive', 'type');
            foreach ($hiveTypes as $cat) 
            {
                $parent = Category::find($cat->parent_id);
                if ($cat->input == 'list_item')
                {
                    $cat->group = $parent->trans();
                    $out['hivetypes'][] = $cat;
                }
            }

            $out['beeraces']                    = Category::descendentsByRootParentAndName('bee_colony', 'characteristics', 'subspecies');
            $out['sensortypes']                 = Category::descendentsByRootParentAndName('hive', 'app', 'sensor');
            $out['hivedimensions']              = Taxonomy::$hive_type_sizes;
            $out['sensormeasurements']          = Measurement::all();
            $out['alert_rules']                 = [];
            $out['alert_rules']['calculations'] = AlertRule::$calculations;
            $out['alert_rules']['comparators']  = AlertRule::$comparators;
            $out['alert_rules']['comparisons']  = AlertRule::$comparisons;
            $out['alert_rules']['exclude_hours']= AlertRule::$exclude_hours;
            $out['alert_rules']['calc_minutes'] = AlertRule::$calc_minutes;

            return $out;
        });

        return response()->json($lists);
    }

    /**
    api/taxonomy/taxonomy 
    List of current state of the standard categories, translated, unordered/ordered in hierachy/flat.
    @queryParam locale string Two character language code to translate taxonomy
    @queryParam flat boolean In hierachy (default: true)
    @queryParam order boolean Ordered (default: false)
    @authenticated
    */
    public function taxonomy(Request $request)
    {
        $locale   = $request->filled('locale') ? $request->input('locale') : LaravelLocalization::getCurrentLocale();
        $order    = ($request->filled('order') && $request->input('order'));
        $flat     = ($request->filled('flat') && $request->input('flat'));
        
        $taxonomy = Cache::remember('taxonomy-'.$locale.'-order-'.$order.'-flat-'.$flat, env('CACHE_TIMEOUT_LONG'), function () use ($locale, $order, $flat)
        {
            $out             = [];
            $out['taxonomy'] = $this->getLanguageOrderedTaxonomy($locale, $order, $flat);
            return $out;
        });

        return response()->json($taxonomy);
    }

    private function getLanguageOrderedTaxonomy($locale='en', $order=false, $flat=false)
    {
        if ($order)
            $cheklistRootNodes = Taxonomy::whereIsRoot()->whereNotIn('type', ['system'])->get()->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->pluck('id');
        else
            $cheklistRootNodes = Taxonomy::whereIsRoot()->whereNotIn('type', ['system'])->pluck('id');
        
        $taxonomy          = collect(); 
        foreach ($cheklistRootNodes as $node)
        {
            if ($flat == true && $order == false)
                $taxonomy = $taxonomy->merge(Taxonomy::whereNotIn('type', ['system'])->descendantsAndSelf($node) );
            else if ($flat == false && $order == false)
                $taxonomy = $taxonomy->merge(Taxonomy::whereNotIn('type', ['system'])->descendantsAndSelf($node)->toTree() );
            else if ($flat == false && $order == true)
                $taxonomy = $taxonomy->merge(Taxonomy::descendantsAndSelf($node)->whereNotIn('type', ['system'])->sortBy("trans.$locale", SORT_NATURAL|SORT_FLAG_CASE)->toTree());
        }

        return $taxonomy;
    }

}
