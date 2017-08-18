<?php

namespace App\Http\Controllers\Api;

use App\Condition;
use App\Category;
use App\Hive;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;
use Auth;

class ConditionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json(
            [
                'hives'=>$request
                    ->user()
                    ->hives()
                    ->with(['conditions' => function($query)
                        {
                          $query->orderBy('created_at', 'desc')->groupBy('category_id','created_at');
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
                    $condition = new Condition();
                    $condition->created_at = $date;
                    $condition->type()->associate($category);
                    $condition->hive()->associate($hive);
                    $itemVal = $item['value'];
                    switch($condition->type)
                    {
                        case "boolean":
                            $condition->boolean = $itemVal > -1 ? (bool)$itemVal : null;
                            break;
                        case "date":
                            $moment_date = new Moment($itemVal);
                            $condition->text = $moment_date->format('Y-m-d'); // Condition has no date field (Action has)
                            break;
                        case "number":
                            $condition->number = (float)$itemVal;
                            break;
                        case "score":
                            $condition->score = $itemVal > -1 ? (integer)$itemVal : null;;
                            break;
                        case "text":
                        case "select":
                        default:
                            $condition->text = (string)$itemVal;
                    }
                    $condition->save();
                }
            }
        }
        if (isset($condition))
            return response()->json(null, 201);
            //return $this->show($request, $condition);

    }

    public function store(Request $request)
    {
        $condition = new Condition();

        if ($request->has('category_id'))
        {
            $condition->type()->associate(Category::findOrFail($request->input('category_id')));
        }
        else
        {
            $condition->type()->associate(Category::name($request->input('category_name'))->first() );
        }
        $condition->hive()->associate(Hive::findOrFail($request->input('hive_id')) );
        $condition->text  = $request->input('text');
        $condition->number= floatval($request->input('number'));
        $condition->score = intval($request->input('score'));
        $condition->boolean= intval($request->input('boolean'));

        $condition->save();

        return $this->show($request, $condition);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Condition  $condition
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Condition $condition)
    {
        return response()->json(['hives'=>[$request->user()->hives()->with('conditions')->findOrFail($condition->hive()->first()->id)]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Condition  $condition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Condition $condition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Condition  $condition
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Condition $condition)
    {
        //
    }
}
