<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CianApiIntegration
{
    protected $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function createLead(array $leadData)
    {
        $mappedData = $this->mapLeadData($leadData);
        $this->validateLeadData($mappedData);

        $response = $this->sendRequest('post', 'https://api.cian.ru/newbuilding-callcenters-stats/v2/create-lead/', $mappedData);

        return $this->handleApiResponse($response);
    }

    public function getNewbuildingsList()
    {
        $response = $this->sendRequest('get', 'https://api.cian.ru/newbuilding-callcenters-stats/v1/get-newbuildings/');
        return $this->handleApiResponse($response);
    }

    public function getPriceRange()
    {
        $response = $this->sendRequest('get', 'https://api.cian.ru/newbuilding-callcenters-stats/v1/get-prices/');
        return $this->handleApiResponse($response);
    }

    private function mapLeadData(array $leadData)
    {
        return [
            'leadPhone' => $leadData['leadPhone'] ?? null,
            'leadName' => $leadData['leadName'] ?? null,
            'classType' => $leadData['classType'] ?? null,
            'maxPrice' => $leadData['maxPrice'] ?? null,
            'minArea' => $leadData['minArea'] ?? null,
            'minPrice' => $leadData['minPrice'] ?? null,
            'region' => $leadData['region'] ?? null,
            'finishYear' => $leadData['finishYear'] ?? null,
            'hasDecoration' => $leadData['hasDecoration'] ?? null,
            'subRegion' => $leadData['subRegion'] ?? null,
            'isApartments' => $leadData['isApartments'] ?? null,
            'isFinished' => $leadData['isFinished'] ?? null,
            'flatType' => $leadData['flatType'] ?? null,
            'searchPeriodInMonth' => $leadData['searchPeriodInMonth'] ?? null,
            'comment' => $leadData['comment'] ?? null,
            'previousRequests' => $leadData['previousRequests'] ?? [],
        ];
    }

    private function validateLeadData(array $data)
    {
        $validator = Validator::make($data, [
            'leadPhone' => 'required|string',
            'leadName' => 'required|string',
            'classType' => 'required|string',
            'maxPrice' => 'required|numeric',
            'minArea' => 'required|numeric',
            'minPrice' => 'required|numeric',
            'region' => 'required|integer',
            'finishYear' => 'required|integer',
            'hasDecoration' => 'required|boolean',
            'subRegion' => 'required|integer',
            'isApartments' => 'required|boolean',
            'isFinished' => 'required|boolean',
            'flatType' => 'required|string',
            'searchPeriodInMonth' => 'required|integer',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function sendRequest(string $method, string $url, array $data = [])
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];

        $request = Http::withHeaders($headers);

        if ($method === 'post') {
            return $request->post($url, $data);
        } elseif ($method === 'get') {
            return $request->get($url);
        }

        return null;
    }

    private function handleApiResponse($response)
    {
        if ($response->successful()) {
            return $response->json();
        }

        return $this->handleApiErrorResponse($response);
    }

    private function handleApiErrorResponse($response)
    {
        $errorData = $response->json();
        $errorMessage = $errorData['error'] ?? 'Неизвестная ошибка';
        $errorCode = $response->status();

        return [
            'status' => 'error',
            'message' => $errorMessage,
            'error_code' => $errorCode,
            'response' => $errorData,
        ];
    }
}
