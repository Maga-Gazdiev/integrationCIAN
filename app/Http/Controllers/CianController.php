<?php

namespace App\Http\Controllers;

use App\Services\CianApiIntegration;
use Illuminate\Http\Request;

class CianController extends Controller
{
    public function createLead(Request $request)
    {
        $accessToken = $request->input('token');

        $cianService = new CianApiIntegration($accessToken);

        $leadData = $request->except('token');

        $response = $cianService->createLead($leadData);

        return response()->json($response);
    }

    public function getNewBuildings(Request $request)
    {
        $accessToken = $request->input('token');
        $cianService = new CianApiIntegration($accessToken);

        $newBuildings = $cianService->getNewbuildingsList();

        return response()->json($newBuildings);
    }

    public function getPriceList(Request $request)
    {
        $accessToken = $request->input('token');
        $cianService = new CianApiIntegration($accessToken);

        $priceList = $cianService->getPriceRange();

        return response()->json($priceList);
    }
}
