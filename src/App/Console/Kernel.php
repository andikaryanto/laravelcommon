<?php

namespace LaravelCommon\App\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function scheduleTenantAwareCommand(Schedule $schedule, string $command): Event
    {
        if (config('multitenancy.enabled', true)) {
            $command = 'tenants:artisan "' . $this->escapeTenantCommand($command) . '"';
        }

        return $schedule->command($command);
    }

    protected function escapeTenantCommand(string $command): string
    {
        return str_replace('"', '\"', $command);
    }
}
