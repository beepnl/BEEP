<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Api\AlertController
 * Manage your alerts
 *
 * @authenticated
 */
class AlertController extends Controller
{
    /**
     * api/alerts GET
     * List all user alerts that are not deleted.
     *
     * @authenticated
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->alerts()->count() > 0) {
            return response()->json(['alerts' => $request->user()->alerts()->get()]);
        }

        return response()->json(['error' => 'no alerts available'], 404);
    }

    /**
     * api/alerts/{id} POST
     * Create the specified user alert.
     *
     * @authenticated
     *
     * @bodyParam alert_rule_id integer required The alert rule that has been alerted for.
     * @bodyParam measurement_id integer required The physical quantity / unit to alert for.
     * @bodyParam alert_value string required The alert value.
     * @bodyParam show boolean Set to false (0) if the alert should NOT be shown anymore.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'alert_rule_id' => 'required|integer|exists:alert_rules,id',
            'measurement_id' => 'required|integer|exists:measurements,id',
            'alert_value' => 'required|string',
        ]);
        $alert = Auth::user()->alerts()->create($request->except('user_id'));

        return response()->json($alert, 201);
    }

    /**
     * api/alerts/{id} GET
     * Display the specified user alert.
     *
     * @authenticated
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $alert = Auth::user()->alerts()->findOrFail($id);

        return $alert;
    }

    /**
     * api/alerts/{id} PATCH
     * Update the specified user alert.
     *
     * @authenticated
     *
     * @bodyParam show boolean Set to false (0) if the alert should NOT be shown anymore.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $alert = Auth::user()->alerts()->findOrFail($id);
        $alert->update($request->except('user_id'));

        return response()->json($alert, 200);
    }

    /**
     * api/alerts/{id} DELETE
     * Delete the specified user alert, or all if id === 'all', or specific id's when provided &alert_ids=1,4,7
     *
     * @authenticated
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if ($request->filled('alert_ids')) {
            Auth::user()->alerts()->whereIn('id', $request->input('alert_ids'))->delete();
        } elseif ($id === 'all') {
            Auth::user()->alerts()->delete();
        } else {
            Auth::user()->alerts()->findOrFail($id)->delete();
        }

        return response()->json(null, 204);
    }
}
