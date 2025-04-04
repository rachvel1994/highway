<?php



use GuzzleHttp\Client;
use Illuminate\Support\Facades\Route;


Route::get('test', function () {

    $client = new Client();

    $payload = [
        'reportType' => 'SALES',
        'buildSummary' => true,
        'groupByRowFields' => [
            'Department',
            'DishType',
            'PayTypes',
            'OrderType',
            'DishName',
            'ItemSaleEventDiscountType',
            'OpenDate',
            'DishReturnSum',
        ],
        'groupByColFields' => [
            'Department',
            'DishType',
            'PayTypes',
            'OrderType',
            'DishName',
            'ItemSaleEventDiscountType',
            'OpenDate',
            'DishReturnSum',
        ],
        'aggregateFields' => [
            'VAT.Sum',
            'Currencies.SumInCurrency',
            'DishReturnSum.withoutVAT',
            'sumAfterDiscountWithoutVAT',
            'DishSumInt',
            'UniqOrderId',
        ],
        'filters' => [
            'DeletedWithWriteoff' => [
                'filterType' => 'ExcludeValues',
                'values' => ['DELETED_WITH_WRITEOFF', 'DELETED_WITHOUT_WRITEOFF']
            ],
            'OrderDeleted' => [
                'filterType' => 'IncludeValues',
                'values' => ['NOT_DELETED']
            ],
            'OpenDate.Typed' => [
                'filterType' => 'DateRange', // Include the values specified in 'values'
                'periodType' => 'CUSTOM', // Replace with actual values you need to filter by
                'from' => '2025-02-01T00:00:00.000', // Replace with actual values you need to filter by
                'to' => '2025-02-28T00:00:00.000', // Replace with actual values you need to filter by
            ]
        ]
    ];

// **DEBUG: Check JSON Payload Before Sending**
    // dd(json_encode($payload, JSON_PRETTY_PRINT));

    try {
        // Move 'key' to the URL as a query parameter
        $url = 'https://restosoft-basic.syrve.online/resto/api/v2/reports/olap?key=58791636-a9c8-d857-3a5e-8265993ced75';

        $response = $client->post($url, [
            'json' => $payload,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Idempotency-Key' => uniqid(),
            ],
            'timeout' => 10,
        ]);

        // Decode JSON Response
        $data = json_decode($response->getBody()->getContents(), true);

        // Debug response
        dd($data);

    } catch (RequestException|\GuzzleHttp\Exception\GuzzleException $e) {
        if ($e->hasResponse()) {
            $errorResponse = $e->getResponse()->getBody()->getContents();
            dd('API Error: ' . $errorResponse);
        }
        dd('Error: ' . $e->getMessage());
    }
});
