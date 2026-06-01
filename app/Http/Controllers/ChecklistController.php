<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Category;
use App\CategoryFactory;
use App\Checklist;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelLocalization;

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
    public function index(Request $request): View
    {

        if (Auth::user()->hasRole('superadmin')) {
            $checklists = CheckList::with('users')->get();
        } else {
            $checklists = $this->getUserChecklists()->get();
        }

        return view('checklists.index', compact('checklists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $taxonomy = Category::getTaxonomy();
        $selected = $this->categoryFactory->get_old_ids_array();
        $users = User::all()->pluck('name', 'id');
        $selectedUserIds = [Auth::user()->id];
        $checklist = Checklist::create([]);

        return view('checklists.create', compact('taxonomy', 'selected', 'users', 'selectedUserIds', 'checklist'));
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {

        $requestData = $request->except(['user_id']);
        $checklist = Checklist::create($requestData);

        $this->addChecklistToUsers($request, $checklist);

        if ($request->filled('categories')) {
            $categories = explode(',', $request->input('categories'));
            $checklist->syncCategories($categories);
        }

        return redirect('checklists')->with('flash_message', 'Checklist added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show(int $id): View
    {
        $checklist = $this->getUserChecklists()->find($id);
        $items = $checklist->categories()->get()->toTree();
        $selected = $items->pluck('id')->toArray();

        return view('checklists.show', compact('checklist', 'items', 'selected'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id): View
    {
        $locale = LaravelLocalization::getCurrentLocale();
        $checklist = $this->getUserChecklists()->find($id);
        $selected = $checklist->categoryIdArray();
        $taxonomy = $checklist->getOrderedChecklist($selected);

        $users = User::all()->pluck('name', 'id');
        $selectedUserIds = $checklist->users()->pluck('id');

        // die(print_r(['id'=>$selectedUserIds, 'cl'=>$checklist->toArray()]));
        return view('checklists.edit', compact('checklist', 'taxonomy', 'selected', 'users', 'selectedUserIds'));
    }

    private function addChecklistToUsers(Request $request, $checklist)
    {
        if ($checklist) {
            $checklist->users()->sync($request->input('user_id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {

        $requestData = $request->except(['user_id']);

        $checklist = $this->getUserChecklists()->find($id);
        $checklist->update($requestData);

        $this->addChecklistToUsers($request, $checklist);

        if ($request->filled('categories')) {
            $categories = explode(',', $request->input('categories'));
            $checklist->syncCategories($categories);
        }

        return redirect('checklists')->with('flash_message', 'Checklist updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->getUserChecklists()->find($id)->delete();

        return redirect('checklists')->with('flash_message', 'Checklist deleted!');
    }

    public function destroyCopies(): RedirectResponse
    {
        Checklist::where('type', 'like', '%_copy%')->forceDelete();

        $checklist_ids = Checklist::pluck('id')->toArray();
        DB::table('checklist_category')->whereNotIn('checklist_id', $checklist_ids)->delete();
        DB::table('checklist_user')->whereNotIn('checklist_id', $checklist_ids)->delete();
        DB::table('checklist_hive')->whereNotIn('checklist_id', $checklist_ids)->delete();

        return redirect('checklists')->with('flash_message', 'All checklist _copy deleted!');
    }

    private function getUserChecklists()
    {
        if (Auth::user()->hasRole('superadmin')) {
            return Checklist::all();
        }

        return Auth::user()->checklists();
    }
}
