<?php

namespace LaravelCommon\App\Trait;

use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;

trait TenantPathCreator
{
    private function currentTenantId(): ?int
    {
        return Tenant::current()?->getKey();
    }

    private function currentTenantName(): ?string
    {
        return Tenant::current()?->name;
    }

    protected function buildTenantPath(): ?string
    {
        return Tenant::current() ? Tenant::current()->name . '/' : null;
    }

    public function ensureTenantPublicPath(string $publicPath): string
    {
        $tenantPath = $this->buildTenantPath() ?? '';
        $fullPath =  'public/' . $tenantPath . $publicPath . '/';

        if (!Storage::exists($fullPath)) {
            Storage::makeDirectory($fullPath);
        }

        return $fullPath;
    }
}
