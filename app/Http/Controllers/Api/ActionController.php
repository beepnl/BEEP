<?php

namespace App\Http\Controllers\Api;

use App\Action;
use App\Category;
use App\Hive;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;
use Auth;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(
            [
                'hives'=>$request
                    ->user()
                    ->hives()
                    ->with(['actions' => function($query)
                        {
                          $query->groupBy('category_id','created_at')->orderBy('created_at');
                        }
                    ])
                    ->get()
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeMultiple(Request $request)
    {
        if ($request->has('multiple_items'))
        {
            $moment = new Moment($request->input('date'));
            $date   = $moment->format('Y-m-d H:i:s');
            $hive   = Auth::user()->hives()->findOrFail($request->input('hive_id'));

            foreach ($request->input('multiple_items') as $item) 
            {
                $category = Category::find($item['id']);
                if (isset($category))
                {
                    $action = new Action();
                    $action->created_at = $date;
                    $action->type()->associate($category);
                    $action->hive()->associate($hive);
                    $itemVal = $item['value'];
                    switch($action->type)
                    {
                        case "boolean":
                            $action->boolean = $itemVal > -1 ? (bool)$itemVal : null;
                            break;
                        case "date":
                            $moment_date = new Moment($itemVal);
                            $action->date = $moment_date->format('Y-m-d'); // Condition has no date field (Action has)
                            break;
                        case "number":
                            $action->number = (float)$itemVal;
                            break;
                        case "score":
                            $action->score = $itemVal > -1 ? (integer)$itemVal : null;
                            break;
                        case "text":
                        case "select":
                        default:
                            $action->text = (string)$itemVal;
                    }
                    $action->save();
                }
            }
        }
        if (isset($action))
            return response()->json(null, 201);
            //return $this->show($request, $action);

    }

    public function store(Request $request)
    {
        $action = new Action();

        if ($request->has('category_id'))
        {
            $action->type()->associate(Category::findOrFail($request->input('category_id')));
        }
        else
        {
            $action->type()->associate(Category::name($request->input('category_name'))->first() );
        }
        $action->hive()->associate(Hive::findOrFail($request->input('hive_id')) );
        $action->text  = $request->input('text');
        $action->number= floatval($request->input('number'));
        $action->score = intval($request->input('score'));
        $action->boolean= intval($request->input('boolean'));
        $action->remind_date = new Moment($request->input('remind_date'));

        $action->save();

        return $this->show($request, $action);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Action  $action
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Action $action)
    {
        return response()->json(['hives'=>[$request->user()->hives()->with('actions')->orderBy('created_at', 'desc')->findOrFail($action->hive()->first()->id)]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Action  $action
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Action $action)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Action  $action
     * @return \Illuminate\Http\Response
     */
    public function destroy(Action $action)
    {
        //
    }
}
