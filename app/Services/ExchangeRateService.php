<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * @return array{base: string, date: string|null, rates: array<string, float>, source: string, error: string|null}
     */
    public function latest(string $base = 'INR'): array
    {
        $cacheKey = 'exchange-rates.'.$base;

        try {
            $rates = Cache::remember($cacheKey, now()->addHour(), function () use ($base) {
                $response = Http::timeout(8)
                    ->retry(2, 200)
                    ->get('https://api.frankfurter.app/latest', [
                        'from' => $base,
                        'to' => 'USD,EUR,GBP,AED,JPY,AUD,CAD,SGD',
                    ]);

                $response->throw();

                /** @var array{base?: string, date?: string, rates?: array<string, float>} $payload */
                $payload = $response->json();

                return [
                    'base' => $payload['base'] ?? $base,
                    'date' => $payload['date'] ?? now()->toDateString(),
                    'rates' => $payload['rates'] ?? [],
                    'source' => 'Frankfurter API (European Central Bank)',
                    'error' => null,
                ];
            });

            Cache::put('exchange-rates.last-known.'.$base, $rates, now()->addDays(30));

            return $rates;
        } catch (RequestException $exception) {
            Log::warning('Exchange rate API request failed.', ['message' => $exception->getMessage()]);

            return $this->unavailable($base, 'The currency API did not respond. Showing the last known rates if available.');
        } catch (\Throwable $exception) {
            Log::warning('Exchange rate lookup failed.', ['message' => $exception->getMessage()]);

            return $this->unavailable($base, 'Currency rates are temporarily unavailable.');
        }
    }

    /**
     * @return array{base: string, date: string|null, rates: array<string, float>, source: string, error: string|null}
     */
    private function unavailable(string $base, string $message): array
    {
        $cached = Cache::get('exchange-rates.last-known.'.$base);

        if (is_array($cached)) {
            $cached['error'] = $message;

            return $cached;
        }

        return [
            'base' => $base,
            'date' => null,
            'rates' => [],
            'source' => 'Frankfurter API (European Central Bank)',
            'error' => $message,
        ];
    }
}
