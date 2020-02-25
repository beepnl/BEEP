<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Taxonomy;
use App\Hive;
use App\Measurement;
use App\Inspection;
use App\InspectionItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;
use Auth;
use LaravelLocalization;

/**
 * @group Api\TaxonomyController
 */
class TaxonomyController extends Controller
{
    
    public function lists(Request $request)
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

        $out['beeraces']       = Category::descendentsByRootParentAndName('bee_colony', 'characteristics', 'subspecies');
        $out['sensortypes']    = Category::descendentsByRootParentAndName('hive', 'app', 'sensor');
        $out['hivedimensions'] = Taxonomy::$hive_type_sizes;
        $out['sensormeasurements'] = Measurement::all();

        return response()->json($out);
    }

    public function taxonomy(Request $request)
    {
        $out = [];
        
        $flat = ($request->filled('flat') && $request->input('flat'));
        $order= ($request->filled('order') && $request->input('order'));

        $out['taxonomy'] = $this->getLanguageOrderedTaxonomy($request, $order, $flat);

        return response()->json($out);
    }

    private function getLanguageOrderedTaxonomy(Request $request, $order, $flat)
    {
        $locale = $request->filled('locale') ? $request->input('locale') : LaravelLocalization::getCurrentLocale();

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
