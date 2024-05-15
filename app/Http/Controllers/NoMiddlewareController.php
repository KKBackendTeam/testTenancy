<?php

namespace App\Http\Controllers;

use App\Traits\TextForSpecificAreaTrait;
use Illuminate\Http\Request;

class NoMiddlewareController extends Controller
{
    use TextForSpecificAreaTrait;

    /**
     * Retrieve thank you page information based on agency ID.
     *
     * @param \Illuminate\Http\Request $request The request containing the agency ID.
     * @return \Illuminate\Http\JsonResponse The JSON response containing thank you page information.
     */
    public function thankYouPageInformation(Request $request)
    {
        $agencyData = agencyDataFormId($request['agency_id']);
        return response()->json(['saved' => true, 'thank_you' => $this->textForSpecificArea('TYP', null, null, $agencyData, null, null, null, null, null)]);
    }
}
