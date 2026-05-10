<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerTag;
use App\Models\EmailList;
use App\Models\ListSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('customer.access:lists.permissions.can_access_lists')->only(['index']);
        $this->middleware('customer.access:lists.permissions.can_edit_lists')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $customer = $request->user('customer');
        $sortBy = (string) $request->input('sort', 'newest');

        $this->syncDiscoveredTags((int) $customer->id);

        $tags = CustomerTag::query()
            ->where('customer_id', $customer->id)
            ->get();

        $usage = $this->buildUsageMap((int) $customer->id, $tags->pluck('name')->all());

        $items = $tags->map(function (CustomerTag $tag) use ($usage) {
            $key = mb_strtolower((string) $tag->name);
            $meta = $usage[$key] ?? ['subscribers' => 0];

            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'description' => $tag->description,
                'subscribers' => (int) ($meta['subscribers'] ?? 0),
                'created_at' => $tag->created_at,
                'updated_at' => $tag->updated_at,
            ];
        });

        $items = match ($sortBy) {
            'oldest' => $items->sortBy(fn (array $item) => $item['created_at']?->timestamp ?? 0)->values(),
            'name_asc' => $items->sortBy(fn (array $item) => mb_strtolower($item['name']))->values(),
            'name_desc' => $items->sortByDesc(fn (array $item) => mb_strtolower($item['name']))->values(),
            'subscribers_desc' => $items->sortByDesc(fn (array $item) => $item['subscribers'])->values(),
            default => $items->sortByDesc(fn (array $item) => $item['created_at']?->timestamp ?? 0)->values(),
        };

        return view('customer.tags.index', [
            'tags' => $items,
            'sortBy' => $sortBy,
            'sortOptions' => [
                'newest' => 'Newest',
                'oldest' => 'Oldest',
                'name_asc' => 'Name A–Z',
                'name_desc' => 'Name Z–A',
                'subscribers_desc' => 'Subscribers',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $customer = $request->user('customer');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $name = $this->normalizeTagName($validated['name']);

        CustomerTag::query()->updateOrCreate(
            ['customer_id' => $customer->id, 'name' => $name],
            ['description' => $this->normalizeDescription($validated['description'] ?? null)]
        );

        return redirect()
            ->route('customer.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, CustomerTag $tag): RedirectResponse
    {
        $this->authorizeOwnership($request, $tag);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldName = (string) $tag->name;
        $newName = $this->normalizeTagName($validated['name']);
        $description = $this->normalizeDescription($validated['description'] ?? null);

        $existing = CustomerTag::query()
            ->where('customer_id', $tag->customer_id)
            ->where('id', '!=', $tag->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($newName)])
            ->first();

        if ($existing) {
            return back()
                ->withErrors(['name' => 'A tag with this name already exists.'])
                ->withInput();
        }

        $tag->update([
            'name' => $newName,
            'description' => $description,
        ]);

        if (mb_strtolower($oldName) !== mb_strtolower($newName)) {
            $this->replaceTagUsage((int) $tag->customer_id, $oldName, $newName);
        }

        return redirect()
            ->route('customer.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Request $request, CustomerTag $tag): RedirectResponse
    {
        $this->authorizeOwnership($request, $tag);

        $tagName = (string) $tag->name;
        $customerId = (int) $tag->customer_id;

        $tag->delete();
        $this->removeTagUsage($customerId, $tagName);

        return redirect()
            ->route('customer.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }

    private function authorizeOwnership(Request $request, CustomerTag $tag): void
    {
        if ((int) $tag->customer_id !== (int) $request->user('customer')->id) {
            abort(404);
        }
    }

    private function normalizeTagName(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function normalizeDescription(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : $value;
    }

    private function buildUsageMap(int $customerId, array $tagNames): array
    {
        $map = [];
        $normalized = collect($tagNames)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->map(fn ($tag) => mb_strtolower($tag))
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return $map;
        }

        $lists = EmailList::query()
            ->where('customer_id', $customerId)
            ->get(['id', 'tags']);

        $listIds = $lists->pluck('id')->all();

        $subscriberTags = ListSubscriber::query()
            ->whereIn('list_id', $listIds)
            ->get(['id', 'tags']);

        foreach ($normalized as $tagKey) {
            $subscriberCount = 0;

            foreach ($subscriberTags as $subscriber) {
                $tags = is_array($subscriber->tags) ? $subscriber->tags : [];
                $hasTag = collect($tags)->contains(fn ($tag) => mb_strtolower(trim((string) $tag)) === $tagKey);
                if ($hasTag) {
                    $subscriberCount++;
                }
            }

            $map[$tagKey] = [
                'subscribers' => $subscriberCount,
            ];
        }

        return $map;
    }

    private function replaceTagUsage(int $customerId, string $oldName, string $newName): void
    {
        $lists = EmailList::query()
            ->where('customer_id', $customerId)
            ->get();

        foreach ($lists as $list) {
            $tags = $this->replaceTagInArray($list->tags, $oldName, $newName);
            if ($tags !== null) {
                $list->update(['tags' => $tags]);
            }
        }

        $subscribers = ListSubscriber::query()
            ->whereIn('list_id', $lists->pluck('id')->all())
            ->get();

        foreach ($subscribers as $subscriber) {
            $tags = $this->replaceTagInArray($subscriber->tags, $oldName, $newName);
            if ($tags !== null) {
                $subscriber->update(['tags' => $tags]);
            }
        }
    }

    private function removeTagUsage(int $customerId, string $tagName): void
    {
        $lists = EmailList::query()
            ->where('customer_id', $customerId)
            ->get();

        foreach ($lists as $list) {
            $tags = $this->removeTagFromArray($list->tags, $tagName);
            if ($tags !== null) {
                $list->update(['tags' => $tags]);
            }
        }

        $subscribers = ListSubscriber::query()
            ->whereIn('list_id', $lists->pluck('id')->all())
            ->get();

        foreach ($subscribers as $subscriber) {
            $tags = $this->removeTagFromArray($subscriber->tags, $tagName);
            if ($tags !== null) {
                $subscriber->update(['tags' => $tags]);
            }
        }
    }

    private function replaceTagInArray(mixed $tags, string $oldName, string $newName): ?array
    {
        if (!is_array($tags)) {
            return null;
        }

        $updated = [];
        $changed = false;
        foreach ($tags as $tag) {
            $value = trim((string) $tag);
            if (mb_strtolower($value) === mb_strtolower($oldName)) {
                $value = $newName;
                $changed = true;
            }
            if ($value !== '') {
                $updated[] = $value;
            }
        }

        $updated = $this->normalizeTagArray($updated);

        return $changed ? $updated : null;
    }

    private function removeTagFromArray(mixed $tags, string $tagName): ?array
    {
        if (!is_array($tags)) {
            return null;
        }

        $changed = false;
        $updated = [];
        foreach ($tags as $tag) {
            $value = trim((string) $tag);
            if ($value === '') {
                continue;
            }
            if (mb_strtolower($value) === mb_strtolower($tagName)) {
                $changed = true;
                continue;
            }
            $updated[] = $value;
        }

        $updated = $this->normalizeTagArray($updated);

        return $changed ? $updated : null;
    }

    private function normalizeTagArray(array $tags): array
    {
        return collect($tags)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->values()
            ->all();
    }

    private function syncDiscoveredTags(int $customerId): void
    {
        $lists = EmailList::query()
            ->where('customer_id', $customerId)
            ->get(['id', 'tags']);

        $discovered = [];

        foreach ($lists as $list) {
            foreach ((is_array($list->tags) ? $list->tags : []) as $tag) {
                $value = trim((string) $tag);
                if ($value !== '') {
                    $discovered[] = $value;
                }
            }
        }

        $listIds = $lists->pluck('id')->all();

        if ($listIds !== []) {
            $subscribers = ListSubscriber::query()
                ->whereIn('list_id', $listIds)
                ->get(['tags']);

            foreach ($subscribers as $subscriber) {
                foreach ((is_array($subscriber->tags) ? $subscriber->tags : []) as $tag) {
                    $value = trim((string) $tag);
                    if ($value !== '') {
                        $discovered[] = $value;
                    }
                }
            }
        }

        $normalized = collect($discovered)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->values();

        foreach ($normalized as $name) {
            $exists = CustomerTag::query()
                ->where('customer_id', $customerId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists();

            if (!$exists) {
                CustomerTag::query()->create([
                    'customer_id' => $customerId,
                    'name' => $name,
                    'description' => null,
                ]);
            }
        }
    }
}
