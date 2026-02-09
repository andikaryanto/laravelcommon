<?php

namespace LaravelCommon\App\Console;

use Illuminate\Console\Command;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class TenantAwareCommand extends Command
{
    use UsesTenantModel;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!config('multitenancy.enabled', true)) {
            return parent::execute($input, $output);
        }

        $tenantModel = $this->getTenantModel();
        $tenantQuery = $tenantModel::query();

        if ($tenantQuery->count() === 0) {
            $this->error('No tenant(s) found.');
            return Command::FAILURE;
        }

        return $tenantQuery
            ->cursor()
            ->map(fn ($tenant) => $tenant->execute(fn () => (int) parent::execute($input, $output)))
            ->sum();
    }
}
