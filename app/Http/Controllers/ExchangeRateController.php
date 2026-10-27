<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use Illuminate\View\View;

class ExchangeRateController extends Controller
{
    public function __invoke(ExchangeRateService $exchangeRates): View
    {
        return view('exchange-rates.index', [
            'exchangeRates' => $exchangeRates->latest(),
        ]);
    }
}
