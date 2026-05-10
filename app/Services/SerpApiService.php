<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerpApiService
{
    private const BASE_URL = 'https://serpapi.com/search.json';

    private const LANGUAGE_NAMES = [
        'af' => 'Afrikaans', 'sq' => 'Albanian', 'ar' => 'Arabic', 'hy' => 'Armenian',
        'az' => 'Azerbaijani', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali',
        'bs' => 'Bosnian', 'bg' => 'Bulgarian', 'ca' => 'Catalan', 'zh' => 'Chinese',
        'zh-CN' => 'Chinese', 'zh-TW' => 'Chinese', 'hr' => 'Croatian', 'cs' => 'Czech',
        'da' => 'Danish', 'nl' => 'Dutch', 'et' => 'Estonian', 'fi' => 'Finnish',
        'fr' => 'French', 'gl' => 'Galician', 'ka' => 'Georgian', 'de' => 'German',
        'el' => 'Greek', 'gu' => 'Gujarati', 'ht' => 'Haitian Creole', 'he' => 'Hebrew',
        'hi' => 'Hindi', 'hu' => 'Hungarian', 'id' => 'Indonesian', 'ga' => 'Irish',
        'it' => 'Italian', 'ja' => 'Japanese', 'kn' => 'Kannada', 'kk' => 'Kazakh',
        'ko' => 'Korean', 'lv' => 'Latvian', 'lt' => 'Lithuanian', 'mk' => 'Macedonian',
        'ms' => 'Malay', 'ml' => 'Malayalam', 'mt' => 'Maltese', 'mr' => 'Marathi',
        'mn' => 'Mongolian', 'ne' => 'Nepali', 'nb' => 'Norwegian', 'no' => 'Norwegian',
        'fa' => 'Persian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'pt-BR' => 'Portuguese',
        'pa' => 'Punjabi', 'ro' => 'Romanian', 'ru' => 'Russian', 'sr' => 'Serbian',
        'sk' => 'Slovak', 'sl' => 'Slovenian', 'es' => 'Spanish', 'sw' => 'Swahili',
        'sv' => 'Swedish', 'tl' => 'Filipino', 'ta' => 'Tamil', 'te' => 'Telugu',
        'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'ur' => 'Urdu',
        'uz' => 'Uzbek', 'vi' => 'Vietnamese', 'cy' => 'Welsh', 'yi' => 'Yiddish',
    ];

    private const US_STATE_MAP = [
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
        'DC' => 'District of Columbia',
    ];

    private function apiKey(): string
    {
        try {
            $key = Setting::get('superscrape_serpapi_key', '');
        } catch (\Throwable $e) {
            $key = '';
        }

        return is_string($key) ? trim($key) : '';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    private function buildMapsQuery(string $query, string $location, string $language): string
    {
        $q = trim($query);

        $normalizedLocation = $location !== '' ? $this->normalizeLocation($location) : '';
        if ($normalizedLocation !== '') {
            $q .= ' in ' . $normalizedLocation;
        }

        $langName = self::LANGUAGE_NAMES[$language] ?? null;
        if ($langName !== null && strtolower($language) !== 'en') {
            $q .= ' with ' . $langName . ' language';
        }

        return $q;
    }

    public function searchMaps(string $query, string $location = '', string $language = 'en', int $limit = 20): array
    {
        $fullQuery = $this->buildMapsQuery($query, $location, $language);

        $params = [
            'engine'        => 'google_maps',
            'type'          => 'search',
            'q'             => $fullQuery,
            'google_domain' => 'google.com',
            'gl'            => 'us',
            'hl'            => $language,
            'api_key'       => $this->apiKey(),
        ];

        return $this->fetchPaginated($params, $limit, 'local_results', $query);
    }

    public function searchPlaces(string $query, string $location = '', string $language = 'en', int $limit = 20): array
    {
        $fullQuery = $this->buildMapsQuery($query, $location, $language);

        $params = [
            'engine'        => 'google_maps',
            'type'          => 'search',
            'q'             => $fullQuery,
            'google_domain' => 'google.com',
            'gl'            => 'us',
            'hl'            => $language,
            'api_key'       => $this->apiKey(),
        ];

        return $this->fetchPaginated($params, $limit, 'local_results', $query);
    }

    public function searchReviews(string $placeId, string $language = 'en', int $limit = 20): array
    {
        $params = [
            'engine'   => 'google_maps_reviews',
            'place_id' => $placeId,
            'hl'       => $language,
            'api_key'  => $this->apiKey(),
        ];

        return $this->fetchPaginated($params, $limit, 'reviews');
    }

    public function searchImages(string $query, string $language = 'en', int $limit = 20): array
    {
        $params = [
            'engine'  => 'google_images',
            'q'       => $query,
            'hl'      => $language,
            'num'     => min($limit, 100),
            'api_key' => $this->apiKey(),
        ];

        try {
            $response = Http::timeout(30)->get(self::BASE_URL, $params);

            if (!$response->successful()) {
                Log::warning('SerpAPI images request failed', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            }

            $data = $response->json();
            return (array) ($data['images_results'] ?? []);
        } catch (\Throwable $e) {
            Log::error('SerpAPI images error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function fetchPaginated(array $params, int $limit, string $resultsKey, ?string $fallbackQuery = null): array
    {
        $allResults = [];
        $start      = 0;
        $pageSize   = 20;
        $retriedFallback = false;

        while (count($allResults) < $limit) {
            $params['start'] = $start;

            try {
                $response = Http::timeout(30)->get(self::BASE_URL, $params);

                if (!$response->successful()) {
                    $body = (string) $response->body();
                    Log::warning('SerpAPI request failed', [
                        'status' => $response->status(),
                        'body'   => substr($body, 0, 500),
                        'params' => array_diff_key($params, ['api_key' => '']),
                    ]);
                    break;
                }

                $data = $response->json();

                if (is_array($data) && !empty($data['error'])) {
                    throw new \RuntimeException((string) $data['error']);
                }

                $results = (array) ($data[$resultsKey] ?? []);

                if (empty($results)) {
                    if (!$retriedFallback && $fallbackQuery !== null && $params['q'] !== $fallbackQuery && $start === 0) {
                        Log::warning('SerpAPI returned empty results with location in query, retrying with query only', [
                            'original_query' => $params['q'],
                            'fallback_query'  => $fallbackQuery,
                        ]);
                        $params['q'] = $fallbackQuery;
                        $retriedFallback = true;
                        $start = 0;
                        $allResults = [];
                        continue;
                    }
                    break;
                }

                $allResults = array_merge($allResults, $results);
                $start += $pageSize;

                if (!isset($data['serpapi_pagination']['next'])) {
                    break;
                }

                usleep(300000);
            } catch (\Throwable $e) {
                Log::error('SerpAPI fetch error', ['error' => $e->getMessage()]);
                break;
            }
        }

        return array_slice($allResults, 0, $limit);
    }

    private function normalizeLocation(string $location): string
    {
        $location = trim($location);

        if ($location === '') {
            return '';
        }

        $parts = array_map(static fn ($part) => trim($part), explode(',', $location));
        $parts = array_values(array_filter($parts, static fn ($part) => $part !== ''));

        foreach ($parts as $index => $part) {
            $upper = strtoupper($part);

            if (isset(self::US_STATE_MAP[$upper])) {
                $parts[$index] = self::US_STATE_MAP[$upper];
                continue;
            }

            if (in_array($upper, ['USA', 'US', 'U.S.A.', 'U.S.'], true)) {
                $parts[$index] = 'United States';
            }
        }

        return implode(', ', $parts);
    }

    public function normalizeMapsResult(array $item): array
    {
        return [
            'name'          => $item['title'] ?? '',
            'email'         => '',
            'phone'         => $item['phone'] ?? '',
            'website'       => $item['website'] ?? '',
            'address'       => $item['address'] ?? '',
            'rating'        => isset($item['rating']) ? (float) $item['rating'] : null,
            'reviews_count' => isset($item['reviews']) ? (int) $item['reviews'] : null,
            'category'      => $item['type'] ?? '',
            'raw_data'      => $item,
        ];
    }

    public function normalizePlacesResult(array $item): array
    {
        return $this->normalizeMapsResult($item);
    }

    public function normalizeReviewsResult(array $item): array
    {
        return [
            'name'          => $item['user']['name'] ?? '',
            'email'         => '',
            'phone'         => '',
            'website'       => $item['user']['link'] ?? '',
            'address'       => '',
            'rating'        => isset($item['rating']) ? (float) $item['rating'] : null,
            'reviews_count' => null,
            'category'      => 'review',
            'raw_data'      => $item,
        ];
    }

    public function normalizeImagesResult(array $item): array
    {
        return [
            'name'          => $item['title'] ?? '',
            'email'         => '',
            'phone'         => '',
            'website'       => $item['source'] ?? ($item['link'] ?? ''),
            'address'       => '',
            'rating'        => null,
            'reviews_count' => null,
            'category'      => 'image',
            'raw_data'      => $item,
        ];
    }
}
