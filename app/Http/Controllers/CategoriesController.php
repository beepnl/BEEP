<?php
namespace App\Http\Controllers;

use DB;
use Image;
use Storage;
use App\Category;
use App\CategoryFactory;
use App\CategoryInput;
use App\PhysicalQuantity;
use App\Translation;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Requests\PostCategoryRequest;
use Illuminate\Http\Request;
use Kalnoy\Nestedset\Collection;
use Illuminate\Support\Str;

class CategoriesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	
	public function __construct(CategoryFactory $categoryFactory)
    {
        $this->categoryFactory = $categoryFactory;
    }

	public function index()
	{
		// $tax = $this->categoryFactory->parse_taxonomy();
		// die(print_r($tax));
        $cats = Category::all();
        $count= $cats->count();
        $tree = $cats->toTree();

		return view('categories.index', compact('tree', 'count'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $input)
	{
        $data = $input->only('parent_id');
        $tree = Category::all()->toTree();

        $categories = $this->getCategoryOptions();

		return view('categories.create', compact('data', 'categories', 'tree'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param PostCategoryRequest $input
     *
     * @return Response
     */
	public function store(PostCategoryRequest $input)
    {
        $category = null;

        if ($input->filled('name'))
        {
            $category = $this->storeOne($input->all());
            if ($category)
                return redirect()->route('categories.show', [ $category->getKey() ])->with('success', 'Category successfully created!');
        }
        elseif ($input->filled('names')) 
        {
            $categories = $this->storeMultiple($input->all());
            if ($categories)
                return redirect()->route('categories.index')->with('success', 'Categories successfully created!');
        }

        return redirect()->route('categories.index')->with('error', 'Category has no name');
    }

    private function storeOne($new)
    {
        if (isset($new['name']) && $new['name'] != null)
        {
            $parent      = isset($inputArray['parent_id']) ? Category::find($inputArray['parent_id']) : null;
            $name        = trim(str_replace(' ', '_', strtolower($new['name'])));
            $new['name'] = $name;
            Translation::createTranslations($name, 'category');
            
            return Category::create($new, $parent);
        }
        return null;
    }

    static function makeCatArray(&$item, $key)
    {
        $item['name'] = $key;
        if ($item->hasChildren())
            $item['children'] = $item->children();
    }


    private function storeMultiple($inputArray)
    {
        // assume text area with lines that are indented by one or multiple spaces in front
        $parent               = isset($inputArray['parent_id']) ? Category::find($inputArray['parent_id']) : null;
        $category_input_id    = isset($inputArray['category_input_id']) ? $inputArray['category_input_id'] : null;
        $physical_quantity_id = isset($inputArray['physical_quantity_id']) ? $inputArray['physical_quantity_id'] : null;

        $assoc_array             = $this->parseLinesToArray($inputArray['names']);

        if (count($assoc_array) > 0)
        {
            foreach ($assoc_array as $name => $children) 
            {
                $cat_array         = $this->splitLineIntoCategoryArray($name, $category_input_id, $physical_quantity_id);
                $base_cat_input_id = $cat_array['category_input_id'];
                $temp_parent       = Category::create($cat_array, $parent);
                Translation::createTranslations($cat_array['name'], 'category');

                if(count($children) > 0) // holds children
                {
                    foreach ($children as $child_name => $sub_children) 
                    {
                        $sub_cat_input_id= $this->getChildInputTypeId($base_cat_input_id); 
                        $cat_array       = $this->splitLineIntoCategoryArray($child_name, $sub_cat_input_id, $physical_quantity_id);
                        $sub_cat_input_id= $cat_array['category_input_id'];
                        $sub_temp_parent = Category::create($cat_array, $temp_parent);
                        Translation::createTranslations($cat_array['name'], 'category');

                        if(count($sub_children) > 0) // holds children
                        {
                            foreach ($sub_children as $sub_child_name => $sub_sub_children) 
                            {
                                $sub_sub_cat_input_id = $this->getChildInputTypeId($sub_cat_input_id); 
                                $cat_array            = $this->splitLineIntoCategoryArray($sub_child_name, $sub_sub_cat_input_id);
                                $sub_sub_cat_input_id = $cat_array['category_input_id'];
                                $sub_sub_temp_parent  = Category::create($cat_array, $sub_temp_parent);
                                Translation::createTranslations($cat_array['name'], 'category');

                                if(count($sub_sub_children) > 0) // holds children
                                {
                                    foreach ($sub_sub_children as $sub_sub_child_name => $sub_sub_sub_children) 
                                    {
                                        $sub_sub_sub_cat_input_id = $this->getChildInputTypeId($sub_sub_cat_input_id); 
                                        $cat_array                = $this->splitLineIntoCategoryArray($sub_sub_child_name, $sub_sub_sub_cat_input_id);
                                        Category::create($cat_array, $sub_sub_temp_parent);
                                        Translation::createTranslations($cat_array['name'], 'category');
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $parent;
        }
        return null;
    }


    private function getChildInputTypeId($category_input_id)
    {
        $category_input          = CategoryInput::find($category_input_id);
        $child_category_input_id = CategoryInput::getTypeId('label');

        if ($category_input)
        {
            switch($category_input->type)
            {
                case "label":
                case "boolean":
                case "boolean_yes_red":
                    $child_category_input_id = CategoryInput::getTypeId('number_positive');
                    break;
                case "list":
                case "select":
                    $child_category_input_id = CategoryInput::getTypeId('list_item');
                    break;
                default:
                    $child_category_input_id = CategoryInput::getTypeId('text_short');
                    break;

            }
        }

        return $child_category_input_id;
    }
    

    private function splitLineIntoCategoryArray($line, $ci_id=null, $pq_id=null)
    {
        $out = ['name'=>'', 'category_input_id'=>$ci_id, 'physical_quantity_id'=>$pq_id, 'description'=>null, 'source'=>null];

        // check for category input type, physical quantity (pq), and description (d)
        $line_arr    = explode('|', $line);
        $out['name'] = str_replace(' ', '_', $line_arr[0]);

        $var_num = count($line_arr);
        if ($var_num > 0)
        {
            for ($i=1; $i < $var_num; $i++) 
            { 
                $var_arr = explode('=', $line_arr[$i]);
                if (count($var_arr) == 2)
                {
                    switch ($var_arr[0]) {
                        case 't':
                           $out['category_input_id'] = CategoryInput::getTypeId($var_arr[1]);
                           break;
                        case 'pq':
                           $out['physical_quantity_id'] = PhysicalQuantity::getAbbreviationId($var_arr[1]);
                           break;
                        case 'pq_id':
                           $out['physical_quantity_id'] = $var_arr[1];
                           break;
                        case 'd':
                           $out['description'] = $var_arr[1];
                           break;
                        case 's':
                           $out['source'] = $var_arr[1];
                           break;
                        default:
                           # code...
                           break;
                    }
                }
            }
        }
        return $out;
    }

    private function parseLinesToArray($list, $indentation = "\t") {
      $result = array();
      $path = array();

      foreach (explode("\r\n", $list) as $line) {
        // get depth and label
        $depth = 0;
        while (substr($line, 0, strlen($indentation)) === $indentation) {
          $depth += 1;
          $line = substr($line, strlen($indentation));
        }

        // truncate path if needed
        while ($depth < sizeof($path)) {
          array_pop($path);
        }

        // keep label (at depth)
        $path[$depth] = $line;

        // traverse path and add label to result
        $parent =& $result;
        foreach ($path as $depth => $key) 
        {
          if (!isset($parent[$key])) 
          {
            $parent[$line] = array();
            break;
          }

          $parent =& $parent[$key];
        }
      }

      // return
      return $result;
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$cats = Category::all();
        $count= $cats->count();
        $tree = $cats->toTree();
        $category = Category::findOrFail($id);
		$categories = $this->getCategoryOptions();

        return view('categories.show', compact('category', 'categories', 'tree', 'count'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$cats = Category::all();
        $count= $cats->count();
        $tree = $cats->toTree();
		$category = Category::findOrFail($id);

		$categories = $this->getCategoryOptions($category);

		return view('categories.edit', compact('category', 'categories', 'tree', 'count'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update(PostCategoryRequest $input, $id)
    {
        $category = Category::findOrFail($id);

        if ($input->filled('name'))
        {
            $name = strtolower(trim($input->input('name')));
            
            $cat_input = 
            [
                'name'                  => $name,
                'source'                => $input->input('source'),
                'type'                  => $input->input('type'),
                'description'           => $input->input('description'),
                'parent_id'             => $input->input('parent_id', null),
                'category_input_id'     => null,
                'physical_quantity_id'  => null,
                'old_id'                => null,
                'required'              => false,
            ];
            
            // Handle the upload of an icon
            if($input->hasFile('icon'))
            {
                $icon = $input->file('icon');
                $ext  = $icon->getClientOriginalExtension();
                $img  = $icon;
                if ($ext != 'svg')
                {
                    $file = Str::random(40).'.'.$ext;
                    Image::make($icon)->resize(100, 100)->save(public_path('/storage/icons/'.$file));
                    $cat_input['icon'] = $file;
                }
                else
                {   
                    $cat_input['icon'] = $img->store('/', 'icons');
                }
            }
            // Handle input_type
            if ($input->has('category_input_id'))
                $cat_input['category_input_id'] = $input->input('category_input_id');

            // Handle physical_quantity
            if ($input->has('physical_quantity_id'))
                $cat_input['physical_quantity_id'] = $input->input('physical_quantity_id');

            // Handle old_id
            if ($input->has('old_id'))
                $cat_input['old_id'] = $input->input('old_id');

            $category->update($cat_input);

            // Handle language
            $createTranslationsForEmptyLanguages = false;
            if ($input->has('language'))
            {
                foreach($input->input('language') as $abbr => $text) 
                {
                    if ($text == '')
                        $createTranslationsForEmptyLanguages = true;
                    else
                        Translation::saveText($abbr, $input->input('name'), 'category', $text);
                }
            }
            else
            {
                $createTranslationsForEmptyLanguages = true;
            }

            if ($createTranslationsForEmptyLanguages)
                Translation::createTranslations($name, 'category');

            return redirect()->route('categories.show', [ $id ])->with('success', 'Category successfully updated!');
        }

        return redirect()->route('categories.show', [ $id ])->with('error', 'Category name is required!');
    }

    public function duplicate($id)
    {
        $category = Category::findOrFail($id);

        $copy = $category->toArray();
        unset($copy['id']);
        $copy['parent_id'] = $category->parent_id;
        $copy['category_input_id'] = $category->category_input_id;
        $copy['physical_quantity_id'] = $category->physical_quantity_id;
        $copy_cat = Category::create($copy);

        if ($category && $category->hasChildren())
            $category->children()->each(
                function($c) use ($copy_cat) 
                {
                    $copy = $c->toArray();
                    unset($copy['id']);
                    $copy['parent_id'] = $copy_cat->id;
                    $copy['category_input_id'] = $c->category_input_id;
                    $copy['physical_quantity_id'] = $c->physical_quantity_id;
                    Category::create($copy);
                }
            );



        return redirect()->route('categories.index')->with('success', 'Category duplicated');
    }


	public function fix($id)
    {
        $inputTypeListId 	 = CategoryInput::where('type','list')->value('id');
        $inputTypeLisItemtId = CategoryInput::where('type','list_item')->value('id');
        $inputLabelItemId 	 = CategoryInput::where('type','label')->value('id');
        $inputSelectItemId 	 = CategoryInput::where('type','select')->value('id');
        $inputOptionsItemId	 = CategoryInput::where('type','options')->value('id');
        $inputBooleanItemId	 = CategoryInput::where('type','boolean')->value('id');
        
        // Change lists with not only list_items for labels
        $listCategories  = Category::whereNotIn('type', ['system'])->where('category_input_id',$inputTypeListId)->get();
        
        //die(print_r($listCategories->toArray()));
        $itemsUnListed   = 0;
        $itemsOptionized = 0;
        $itemsBooleanized= 0;
        foreach ($listCategories as $cat) 
        {
        	//die(print_r($cat->children()->get()->toArray()));
        	$childCount = $cat->children()->count();
        	if ($childCount > 0)
        	{
        		$onlyListItems = true;
	        	foreach ($cat->children()->get() as $child) 
	        	{
	        		if ($child->category_input_id != $inputTypeLisItemtId)
	        			$onlyListItems = false;
	        		
	        	}
	        	if ($onlyListItems) // make options (select ONE option)
	        	{
	        		// $cat->category_input_id = $inputOptionsItemId;
	        		// $itemsOptionized += $cat->save();
	        	}
	        	else // make label (category holder)
	        	{
	        		$cat->category_input_id = $inputLabelItemId;
	        		$itemsUnListed += $cat->save();
	        	}
	        }
	        else // make Boolean (checkbox)
	        {
	        	// $cat->category_input_id = $inputBooleanItemId;
	        	// $itemsBooleanized += $cat->save();
	        }
        }

        // Change options items with explicit 4 categories to score
        $optionsCategories  = Category::whereNotIn('type', ['system'])->whereIn('category_input_id',[$inputOptionsItemId,$inputSelectItemId])->hasChildren()->get();
        //die(print_r($listCategories->toArray()));
        $itemsScoreized = 0;
        foreach ($optionsCategories as $cat) 
        {
        	$testArray  = [ 
        		'score_quality'=>['poor','fair','good','excellent'],
        		'score_amount'=>['low','medium','high','extreme']
        	];
        	
        	$childCount = $cat->children()->count();
        	if ($childCount == 4)
        	{
	        	$optionsString = implode($cat->children()->pluck('name')->toArray());
        		foreach ($testArray as $replaceBy => $checkValues) 
        		{
        			$replaceCategoryInputId	 = CategoryInput::where('type',$replaceBy)->value('id');
        			
        			if ($replaceCategoryInputId && implode($checkValues) == $optionsString)
        			{
	        			//die(print_r( [$optionsString, implode($checkValues)] ));
        				$cat->category_input_id = $replaceCategoryInputId;
        				if ($cat->save())
        				{
	        				$itemsScoreized++;
	        				foreach ($cat->children()->get() as $opt) 
	        				{
	        					$opt->delete();
	        				}
        				}
        			}
        		}
        	}
        }

        // Change label items with only list_items for select items
        $labelCategories  = Category::whereNotIn('type', ['system'])->where('category_input_id',$inputLabelItemId)->hasChildren()->get();
        //die(print_r($listCategories->toArray()));
        $itemsUnLabeled = 0;
        foreach ($labelCategories as $cat) 
        {
        	$onlyListItems = true;
        	//die(print_r($cat->children()->get()->toArray()));
        	foreach ($cat->children()->get() as $child) 
        	{
        		if ($child->category_input_id != $inputTypeLisItemtId)
        			$onlyListItems = false;
        		
        	}
        	if ($onlyListItems == true)
        	{
        		//die(print_r(['id'=>$inputLabelItemId, 'cat'=>$cat->toArray()]));
        		$cat->category_input_id = $inputSelectItemId;
        		$itemsUnLabeled += $cat->save();
        	}
        }

        Category::fixTree(); // kalnoy/nestedset: to fix the tree to fill _lft and _rgt columns

        return redirect()->route('categories.index')->with('success', "Taxonomy successfully fixed! Changed $itemsUnListed list items to label. Changed $itemsUnLabeled label items to select. Changed $itemsOptionized items from list to options. Changed $itemsBooleanized empty lists to boolean. Converted $itemsScoreized options lists with score to score (5 stars) item.");
    }

	public function pop($id)
    {
        $category = Category::findOrFail($id);

        if ($category && $category->useAmount() == 0 && $category->hasChildren())
        {

	        	if ($category->isRoot())
	        	{
	        		$category->children()->each(function($c){$c->saveAsRoot();});
	        	}
	        	else
	        	{
	        		$p_id = $category->parent()->value('id');

        			foreach ($category->children()->get() as $c) 
        			{
	        			$c->parent_id = $p_id;
	        			$c->save();
	        		};
	        	}
        }

        return $this->destroy($id);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category)
        {
            if ($category->useAmount() > 0)
                return redirect()->route('categories.index')->with('error','Category is used '.$category->useAmount().'x, so cannot be deleted');

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            //$category->children()->each(function($c){$c->delete();});
            $category->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->route('categories.index')->with('success','Category deleted successfully');
        }

        return redirect()->route('categories.index')->with('error','Category not found');
    }

    /**
     * @param Collection $items
     *
     * @return static
     */
    protected function makeOptions(Collection $items)
    {
        $options = [ '' => 'Root' ];

        foreach ($items as $i => $item)
        {
            $options[$item->getKey()] = $i.' (depth: '.$item->depth.')'.str_repeat('‒', $item->depth + 1).' '.$item->name;
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
