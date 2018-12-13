<?php
namespace App\Http\Controllers;

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
		$category = Category::create($input->all());

        return redirect()
            ->route('categories.show', [ $category->getKey() ])
            ->with('success', 'Category successfully created!');
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
		/** @var Category $category */
		$category = Category::findOrFail($id);

		//die(print_r($input->all()));

		$cat_input = 
		[
			'name' 		  			=> strtolower(trim($input->input('name'))),
			'source' 	  			=> $input->input('source'),
			'type' 					=> $input->input('type'),
			'description' 			=> $input->input('description'),
			'parent_id' 			=> $input->input('parent_id'),
			'category_input_id' 	=> null,
			'physical_quantity_id' 	=> null,
			'old_id' 	            => null,
		];
		
		// Handle the upload of an icon
        if($input->hasFile('icon'))
        {
            $icon = $input->file('icon');
            $ext  = $icon->getClientOriginalExtension();
            $img  = $icon;
            if ($ext != 'svg')
            {
            	$file = str_random(40).'.'.$ext;
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
        if ($input->has('language'))
        {
        	foreach($input->input('language') as $abbr => $text) 
        	{
        		Translation::saveText($abbr, $input->input('name'), 'category', $text);
        	}
        }
        //die(print_r($cat_input));

		return redirect()->route('categories.show', [ $id ])->with('success', 'Category successfully updated!');
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

            $category->delete();
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
