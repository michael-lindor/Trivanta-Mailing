<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers') || !Schema::hasTable('customer_groups') || !Schema::hasTable('customer_customer_group')) {
            return;
        }

        $roleGroupId = $this->resolveRoleGroupId();
        if (!$roleGroupId) {
            return;
        }

        $now = now();

        $missingCustomerIds = DB::table('customers as c')
            ->leftJoin('customer_customer_group as ccg', 'ccg.customer_id', '=', 'c.id')
            ->whereNull('ccg.customer_id')
            ->select('c.id')
            ->pluck('c.id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        if (empty($missingCustomerIds)) {
            return;
        }

        $rows = array_map(function (int $customerId) use ($roleGroupId, $now): array {
            return [
                'customer_id' => $customerId,
                'customer_group_id' => $roleGroupId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $missingCustomerIds);

        DB::table('customer_customer_group')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        // No-op: this is a one-time data backfill migration.
    }

    private function resolveRoleGroupId(): ?int
    {
        if (Schema::hasTable('settings')) {
            $configuredGroupIds = DB::table('settings')
                ->whereIn('key', ['new_registered_customer_group_id', 'default_customer_group_id'])
                ->pluck('value')
                ->map(function ($value) {
                    $id = (int) $value;
                    return $id > 0 ? $id : null;
                })
                ->filter()
                ->values();

            foreach ($configuredGroupIds as $groupId) {
                if (DB::table('customer_groups')->where('id', (int) $groupId)->exists()) {
                    return (int) $groupId;
                }
            }
        }

        $fallbackGroup = DB::table('customer_groups')
            ->select('id')
            ->orderByRaw('LOWER(name) = ? DESC', ['customer'])
            ->orderBy('id')
            ->first();

        return $fallbackGroup ? (int) $fallbackGroup->id : null;
    }
};
