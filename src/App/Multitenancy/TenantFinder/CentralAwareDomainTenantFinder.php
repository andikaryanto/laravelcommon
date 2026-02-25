<?php

namespace LaravelCommon\App\Multitenancy\TenantFinder;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\DomainTenantFinder;

class CentralAwareDomainTenantFinder extends DomainTenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();

        if ($this->isCentralDomain($host)) {
            return null;
        }

        return parent::findForRequest($request);
    }

    protected function isCentralDomain(string $host): bool
    {
        $centralDomains = (array) config('multitenancy.central_domains', []);
        if (in_array($host, $centralDomains, true)) {
            return true;
        }

        $reservedSubdomains = (array) config('multitenancy.reserved_subdomains', ['landlord']);
        $segments = explode('.', $host);
        $subdomain = $segments[0] ?? null;

        return $subdomain !== null && in_array($subdomain, $reservedSubdomains, true);
    }
}
