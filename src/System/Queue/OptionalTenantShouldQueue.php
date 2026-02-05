<?php

namespace LaravelCommon\System\Queue;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\Multitenancy\Jobs\TenantAware;
use Spatie\Multitenancy\Models\Tenant;

abstract class OptionalTenantShouldQueue implements ShouldQueue, TenantAware
{
    protected ?int $tenantId = null;

    protected function captureTenantContext(): void
    {
        $this->tenantId = Tenant::current()?->getKey();
    }

    protected function ensureTenantContext(): void
    {
        if (!config('multitenancy.enabled', true)) {
            return;
        }

        if (Tenant::current()) {
            return;
        }

        $tenant = $this->tenantId ? Tenant::find($this->tenantId) : null;
        if ($tenant) {
            $tenant->makeCurrent();
            return;
        }

        throw new \RuntimeException('Tenant context is missing for tenant-aware job.');
    }
}
