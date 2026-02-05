<?php

namespace LaravelCommon\App\Trait;

use Spatie\Multitenancy\Models\Tenant;

trait UsesCurrentTenant
{
    protected function currentTenantId(): ?int
    {
        return Tenant::current()?->getKey();
    }

    protected function currentTenantName(): ?string
    {
        return Tenant::current()?->name;
    }

    protected function buildTenantPath(): ?string
    {
        return Tenant::current()?->name . '/';
    }
}
