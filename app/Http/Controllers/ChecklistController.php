<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use App\User;
use App\Checklist;
use App\Language;
use App\Category;
use App\CategoryFactory;
use App\CategoryInput;
use Illuminate\Http\Request;
use LaravelLocalization;

use Illuminate\Support\Facades\DB;

class ChecklistController extends Controller
{
    public function __construct(CategoryFactory $categoryFactory)
    {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        
        if (Auth::user()->hasRole('superadmin'))
            $checklists = CheckList::all();
        else
            $checklists = $this->getUserChecklists()->get();
            
        return view('checklists.index', compact('checklists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $taxonomy       = Category::getTaxonomy();
        $selected       = $this->categoryFactory->get_old_ids_array();
        $users          = User::all()->pluck('name','id');
        $selectedUserIds= [Auth::user()->id];
        $checklist      = Checklist::create([]);

        return view('checklists.create', compact('taxonomy', 'selected', 'users', 'selectedUserIds', 'checklist'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $requestData = $request->except(['user_id']);
        $checklist   = Checklist::create($requestData);

        $this->addChecklistToUser($request, $checklist);

        if ($request->filled('categories'))
        {
            $categories = explode(',', $request->input('categories'));
            $checklist->syncCategories($categories);
        }

        return redirect('checklists')->with('flash_message', 'Checklist added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $checklist = $this->getUserChecklists()->find($id);
        $items     = $checklist->categories()->get()->toTree();
        $selected  = $items->pluck('id')->toArray();

        return view('checklists.show', compact('checklist', 'items', 'selected'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $locale    = LaravelLocalization::getCurrentLocale();
        $checklist = $this->getUserChecklists()->find($id);
        $selected  = $checklist->categoryIdArray();
        $taxonomy  = $checklist->getOrderedChecklist($selected);
        
        $users     = User::all()->pluck('name','id');
        $selectedUserIds = $checklist->users()->pluck('id');

        //die(print_r(['id'=>$selectedUserIds, 'cl'=>$checklist->toArray()]));
        return view('checklists.edit', compact('checklist', 'taxonomy', 'selected', 'users', 'selectedUserIds'));
    }

    private function addChecklistToUser(Request $request, $checklist)
    {
        if ($request->filled('user_id') && $checklist)
        {
            $user_id = $request->input('user_id');
            $user    = User::find($user_id);

            if (User::where('id', $user_id)->count() == 1 && $user->checklists()->pluck('id')->search($checklist->id) === false)
                $user->checklists()->attach($checklist);
        }
        else
        {
            $checklist->users()->detach();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        
        $requestData = $request->except(['user_id']);
        
        $checklist = $this->getUserChecklists()->find($id);
        $checklist->update($requestData);

        $this->addChecklistToUser($request, $checklist);

        if ($request->filled('categories'))
        {
            $categories = explode(',', $request->input('categories'));
            $checklist->syncCategories($categories);
        }

        return redirect('checklists')->with('flash_message', 'Checklist updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $this->getUserChecklists()->find($id)->delete();

        return redirect('checklists')->with('flash_message', 'Checklist deleted!');
    }

    public function destroyCopies()
    {
        Checklist::where('type','like','%_copy%')->forceDelete();

        $checklist_ids = Checklist::pluck('id')->toArray();
        DB::table('checklist_category')->whereNotIn('checklist_id', $checklist_ids)->delete();
        DB::table('checklist_user')->whereNotIn('checklist_id', $checklist_ids)->delete();
        DB::table('checklist_hive')->whereNotIn('checklist_id', $checklist_ids)->delete();

        return redirect('checklists')->with('flash_message', 'All checklist _copy deleted!');
    }

    private function getUserChecklists()
    {
        if (Auth::user()->hasRole('superadmin'))
        {
            return Checklist::all();
        }
        return Auth::user()->checklists();
    }
}
