<?php
namespace App\Http\Controllers;

use App\Category;
use App\Http\Controllers\Controller;
use LaravelLocalization;

class TaxonomyController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	

    public function display()
    {
        $locale 	= LaravelLocalization::getCurrentLocale();
        $categories = Category::getTaxonomy(null, false, true);


        $cat_names  = [];
        foreach ($categories as $id => $cat) 
        {
       		if ($cat->isSystem() == false)
       			$cat_names[' .'.$cat->ancName($locale, '.').$cat->transName($locale)] = $cat->rootNodeName();
        }

        ksort($cat_names);

        $cats       = [['id'=>' ','base'=>'']];
        foreach ($cat_names as $id => $base) 
        {
       		array_push($cats, ['id'=>$id, 'base'=>$base]);
        }

        //die(print_r($cats));
        
        $count		= $categories->count()-1;
        $catsJson 	= json_encode($cats);
        

        return view('taxonomy.display', compact('catsJson','count'));
    }

}
