<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
use Illuminate\Console\Command;

class BackfillCustomerRoleAssignments extends Command
{
    protected $signature = 'customers:backfill-role-assignments
                            {--dry-run : Preview changes without writing}
                            {--role-id= : Force a specific customer_group id}
                            {--limit=0 : Max number of customers to process (0 = all)}';

    protected $description = 'Assign a default RBAC role (customer group) to existing customers that have no groups.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $forcedRoleId = $this->option('role-id');
        $limit = max(0, (int) $this->option('limit'));

        $targetRoleId = $this->resolveTargetRoleId($forcedRoleId);
        if (!$targetRoleId) {
            $this->error('No eligible customer role group found. Set new_registered_customer_group_id/default_customer_group_id or pass --role-id.');
            return Command::FAILURE;
        }

        $targetRole = CustomerGroup::query()->find($targetRoleId);
        if (!$targetRole) {
            $this->error('Resolved role group no longer exists.');
            return Command::FAILURE;
        }

        $query = Customer::query()
            ->whereDoesntHave('customerGroups')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $customers = $query->get(['id', 'email']);

        if ($customers->isEmpty()) {
            $this->info('No customers without role groups were found.');
            return Command::SUCCESS;
        }

        $processed = 0;

        foreach ($customers as $customer) {
            if ($dryRun) {
                $this->line("[dry-run] customer#{$customer->id} {$customer->email} -> role#{$targetRole->id} {$targetRole->name}");
                $processed++;
                continue;
            }

            $customer->customerGroups()->syncWithoutDetaching([$targetRole->id]);
            $processed++;
        }

        $this->info(sprintf(
            'Backfill complete. role#%d (%s), processed=%d%s',
            $targetRole->id,
            (string) $targetRole->name,
            $processed,
            $dryRun ? ' (dry-run)' : ''
        ));

        return Command::SUCCESS;
    }

    private function resolveTargetRoleId(mixed $forcedRoleId): ?int
    {
        if ($forcedRoleId !== null && $forcedRoleId !== '') {
            $id = (int) $forcedRoleId;
            if ($id > 0 && CustomerGroup::query()->whereKey($id)->exists()) {
                return $id;
            }

            return null;
        }

        $newRegisteredGroupId = Setting::get('new_registered_customer_group_id');
        if ($newRegisteredGroupId && CustomerGroup::query()->whereKey((int) $newRegisteredGroupId)->exists()) {
            return (int) $newRegisteredGroupId;
        }

        $defaultGroupId = Setting::get('default_customer_group_id');
        if ($defaultGroupId && CustomerGroup::query()->whereKey((int) $defaultGroupId)->exists()) {
            return (int) $defaultGroupId;
        }

        $fallbackRoleGroup = CustomerGroup::query()
            ->orderByRaw('LOWER(name) = ? DESC', ['customer'])
            ->orderBy('id')
            ->first();

        return $fallbackRoleGroup ? (int) $fallbackRoleGroup->id : null;
    }
}
