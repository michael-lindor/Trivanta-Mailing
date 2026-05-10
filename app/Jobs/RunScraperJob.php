<?php

namespace App\Jobs;

use App\Models\ScraperJob;
use App\Models\ScraperLead;
use App\Services\SerpApiService;
use App\Services\SerperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunScraperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(
        public readonly int $scraperJobId
    ) {}

    public function handle(SerpApiService $serpApi, SerperService $serper): void
    {
        $job = ScraperJob::find($this->scraperJobId);

        if (!$job) {
            return;
        }

        $location = $job->location ?? '';
        $language = $job->language ?? 'en';
        $apiQuery = $job->query;
        if ($location !== '') {
            $apiQuery .= ' in ' . $location;
        }
        if (!in_array(strtolower($language), ['en', ''], true)) {
            $apiQuery .= ' with ' . $language . ' language';
        }

        $debugData = [
            'started_at' => now()->toISOString(),
            'job_id' => $job->id,
            'type' => $job->type,
            'query' => $job->query,
            'api_query' => $apiQuery,
            'location' => $location,
            'language' => $job->language,
            'max_results' => $job->max_results,
            'extract_emails' => (bool) $job->extract_emails,
        ];

        $job->update([
            'status' => 'running',
            'debug_data' => $debugData,
        ]);

        try {
            $rawResults = $this->fetchResults($job, $serpApi, $serper);
            $debugData['raw_results_count'] = count($rawResults);
            $debugData['raw_result_sample'] = !empty($rawResults)
                ? $this->truncateDebugValue($rawResults[0])
                : null;

            $leads   = [];
            $credits = 0;

            foreach ($rawResults as $item) {
                $normalized = $this->normalizeResult($job->type, $item, $serpApi, $serper);

                if (empty($normalized)) {
                    continue;
                }

                $leadRow = array_merge($normalized, [
                    'job_id'      => $job->id,
                    'customer_id' => $job->customer_id,
                    'source_type' => $job->type,
                ]);

                $leads[] = $this->prepareLeadForInsert($leadRow);

                $credits++;
            }

            $debugData['normalized_results_count'] = count($leads);
            $debugData['normalized_result_sample'] = !empty($leads)
                ? $this->truncateDebugValue($leads[0])
                : null;

            $creditsCost = (int) ceil($credits / 10);

            if (!empty($leads)) {
                foreach ($leads as $lead) {
                    ScraperLead::create($lead);
                }
            }

            $debugData['credits_used'] = $creditsCost;
            $debugData['completed_at'] = now()->toISOString();
            $debugData['message'] = $credits === 0
                ? 'Job completed but no normalized results were extracted.'
                : 'Job completed successfully.';

            $job->update([
                'status'       => 'completed',
                'records_count' => $credits,
                'credits_used'  => $creditsCost,
                'debug_data'    => $debugData,
                'completed_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            $debugData['failed_at'] = now()->toISOString();
            $debugData['exception_message'] = $e->getMessage();
            $debugData['exception_class'] = get_class($e);

            Log::error('RunScraperJob failed', [
                'job_id' => $this->scraperJobId,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);

            $job->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'debug_data'    => $debugData,
                'completed_at'  => now(),
            ]);
        }
    }

    private function truncateDebugValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $json = json_encode($value);

        if ($json === false || strlen($json) <= 4000) {
            return $value;
        }

        return [
            'preview' => substr($json, 0, 4000),
            'truncated' => true,
        ];
    }

    private function prepareLeadForInsert(array $leadRow): array
    {
        foreach (['name', 'email', 'phone', 'website', 'address', 'category', 'source_type'] as $key) {
            if (!array_key_exists($key, $leadRow) || $leadRow[$key] === null) {
                continue;
            }

            if (is_array($leadRow[$key]) || is_object($leadRow[$key])) {
                $json = json_encode($leadRow[$key], JSON_UNESCAPED_SLASHES);
                $leadRow[$key] = $json === false ? '' : $json;
            } else {
                $leadRow[$key] = (string) $leadRow[$key];
            }
        }

        if (array_key_exists('raw_data', $leadRow)) {
            $raw = $leadRow['raw_data'];
            if ($raw === null) {
                $leadRow['raw_data'] = null;
            } else {
                $encoded = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $leadRow['raw_data'] = ($encoded === false) ? null : $encoded;
            }
        }

        if (array_key_exists('rating', $leadRow) && $leadRow['rating'] !== null) {
            $leadRow['rating'] = (float) $leadRow['rating'];
        }

        if (array_key_exists('reviews_count', $leadRow) && $leadRow['reviews_count'] !== null) {
            $leadRow['reviews_count'] = (int) $leadRow['reviews_count'];
        }

        return $leadRow;
    }

    private function fetchResults(ScraperJob $job, SerpApiService $serpApi, SerperService $serper): array
    {
        $limit    = min($job->max_results, 200);
        $language = $job->language ?? 'en';
        $location = $job->location ?? '';
        $query    = $job->query;

        return match ($job->type) {
            'maps'    => $serpApi->searchMaps($query, $location, $language, $limit),
            'places'  => $serpApi->searchPlaces($query, $location, $language, $limit),
            'reviews' => $serpApi->searchReviews($query, $language, $limit),
            'images'  => $serpApi->searchImages($query, $language, $limit),
            'news'    => $serper->searchNews($query, $language, $limit),
            default   => [],
        };
    }

    private function normalizeResult(string $type, array $item, SerpApiService $serpApi, SerperService $serper): array
    {
        return match ($type) {
            'maps'    => $serpApi->normalizeMapsResult($item),
            'places'  => $serpApi->normalizePlacesResult($item),
            'reviews' => $serpApi->normalizeReviewsResult($item),
            'images'  => $serpApi->normalizeImagesResult($item),
            'news'    => $serper->normalizeNewsResult($item),
            default   => [],
        };
    }

    public function failed(\Throwable $e): void
    {
        $job = ScraperJob::find($this->scraperJobId);
        if ($job) {
            $job->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);
        }
    }
}
