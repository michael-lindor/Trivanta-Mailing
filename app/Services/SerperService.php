<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerperService
{
    private const NEWS_URL   = 'https://google.serper.dev/news';
    private const SEARCH_URL = 'https://google.serper.dev/search';

    private function apiKey(): string
    {
        try {
            $key = Setting::get('superscrape_serper_key', '');
        } catch (\Throwable $e) {
            $key = '';
        }

        return is_string($key) ? trim($key) : '';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function searchNews(string $query, string $language = 'en', int $limit = 20): array
    {
        $allResults = [];
        $page       = 1;
        $pageSize   = min($limit, 10);

        while (count($allResults) < $limit) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'X-API-KEY'    => $this->apiKey(),
                        'Content-Type' => 'application/json',
                    ])
                    ->post(self::NEWS_URL, [
                        'q'    => $query,
                        'num'  => $pageSize,
                        'hl'   => $language,
                        'page' => $page,
                    ]);

                if (!$response->successful()) {
                    Log::warning('Serper news request failed', [
                        'status' => $response->status(),
                        'body'   => substr($response->body(), 0, 500),
                    ]);
                    break;
                }

                $data    = $response->json();
                $results = (array) ($data['news'] ?? []);

                if (empty($results)) {
                    break;
                }

                $allResults = array_merge($allResults, $results);
                $page++;

                usleep(300000);
            } catch (\Throwable $e) {
                Log::error('Serper news error', ['error' => $e->getMessage()]);
                break;
            }
        }

        return array_slice($allResults, 0, $limit);
    }

    public function normalizeNewsResult(array $item): array
    {
        return [
            'name'          => $item['title'] ?? '',
            'email'         => '',
            'phone'         => '',
            'website'       => $item['link'] ?? '',
            'address'       => $item['source'] ?? '',
            'rating'        => null,
            'reviews_count' => null,
            'category'      => 'news',
            'raw_data'      => $item,
        ];
    }
}
